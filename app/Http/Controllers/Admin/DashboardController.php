<?php

/**
 * Guardar como: app/Http/Controllers/Admin/DashboardController.php
 *
 * Regra de leitura do painel:
 *  - Declaração: cada submissão do formulário. Mede o volume de trabalho e a
 *    cobertura da medida de saúde pública.
 *  - Viajante: passaporte distinto. O mesmo viajante pode declarar várias
 *    entradas, por isso os dois números nunca coincidem.
 *  - Alerta: declaração com pelo menos uma resposta afirmativa no questionário
 *    clínico ou com passagem por país sob vigilância. É o indicador que
 *    determina o encaminhamento no ponto de entrada.
 *
 * O painel distingue sempre o passado (o que já chegou) do futuro (chegadas
 * previstas), porque são leituras operacionais diferentes.
 */

namespace App\Http\Controllers\Admin;

use App\Classes\CountryFlag;
use App\Classes\Logger;
use App\Http\Controllers\Controller;
use App\Models\TravelerDeclaration;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    private $Logger;

    /** Janela retrospectiva dos gráficos de evolução, em dias */
    const HISTORY_DAYS = 14;

    /** Janela prospectiva das chegadas previstas, em dias */
    const FORECAST_DAYS = 7;

    /** Período de incubação vigiado pelo formulário, em dias */
    const SURVEILLANCE_WINDOW = 21;

    /**
     * Rótulos curtos das perguntas clínicas, para os eixos dos gráficos.
     * A chave é a coluna, tal como em TravelerDeclaration::CLINICAL_QUESTIONS.
     */
    private $symptomShortLabels = [
        'evd_contact'    => 'Contacto com caso suspeito (DVE)',
        'fever'          => 'Febre (>= 38,5 °C)',
        'headache'       => 'Cefaleia',
        'myalgia'        => 'Mialgia',
        'weakness'       => 'Fraqueza ou fadiga',
        'bleeding'       => 'Manifestações hemorrágicas',
        'other_symptoms' => 'Outros sintomas',
    ];

    public function __construct()
    {
        $this->Logger = new Logger();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $countries = $this->countryBreakdown();

        $response = [
            'counters'      => $this->counters(count($countries)),
            'countries'     => $countries,
            'symptoms'      => $this->symptomBreakdown(),
            'daily'         => $this->dailyMovement(self::HISTORY_DAYS),
            'forecast'      => $this->arrivalForecast(self::FORECAST_DAYS),
            'purposes'      => $this->purposeBreakdown(),
            'airlines'      => $this->columnBreakdown('airline_name', 8),
            'riskCountries' => $this->riskCountryBreakdown(),
            'ageGroups'     => $this->ageBreakdown(),
            'alertFlights'  => $this->flightsWithAlerts(8),
        ];

        $this->Logger->log('info', 'Entrou no Painel Administrativo');

        return view('admin.home.index', $response);
    }

    /**
     * Todas as declarações submetidas.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function declarations()
    {
        return TravelerDeclaration::query();
    }

    /**
     * Declarações que exigem triagem sanitária: sintoma declarado ou passagem
     * por país sob vigilância.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function alerts()
    {
        return TravelerDeclaration::query()->where(function ($query) {
            $query->withAlerts()
                ->orWhere(function ($sub) {
                    $sub->whereNotNull('countries_visited')
                        ->where('countries_visited', '<>', '[]');
                });
        });
    }

    /**
     * Contadores de topo.
     *
     * @param  int  $countryCount
     * @return array
     */
    private function counters($countryCount)
    {
        $total = (int) $this->declarations()->count();
        $alerts = (int) $this->alerts()->count();

        $arrivingToday = (int) $this->declarations()
            ->whereDate('arrival_date', Carbon::today())
            ->count();

        $arrivingWeek = (int) $this->declarations()
            ->whereBetween('arrival_date', [
                Carbon::today()->toDateString(),
                Carbon::today()->addDays(self::FORECAST_DAYS - 1)->toDateString(),
            ])
            ->count();

        return [
            // Volume
            'total'        => $total,
            'travelers'    => (int) $this->declarations()->distinct('passport_number')->count('passport_number'),
            'countries'    => (int) $countryCount,
            'today'        => (int) $this->declarations()->whereDate('submitted_at', Carbon::today())->count(),
            'last7'        => (int) $this->declarations()
                ->where('submitted_at', '>=', Carbon::today()->subDays(6)->startOfDay())
                ->count(),

            // Triagem sanitária
            'alerts'       => $alerts,
            'alert_rate'   => $total > 0 ? round($alerts * 100 / $total, 1) : 0,
            'symptomatic'  => (int) TravelerDeclaration::withAlerts()->count(),
            'risk_exposed' => (int) $this->declarations()
                ->whereNotNull('countries_visited')
                ->where('countries_visited', '<>', '[]')
                ->count(),
            'verified'     => (int) $this->declarations()->where('status', 'VERIFIED')->count(),
            'pending'      => (int) $this->declarations()->where('status', 'ALERT')->count(),

            // Operação nos pontos de entrada
            'arriving_today' => $arrivingToday,
            'arriving_week'  => $arrivingWeek,
            'alerts_ahead'   => (int) $this->alerts()
                ->whereDate('arrival_date', '>=', Carbon::today())
                ->count(),
            'window'         => self::SURVEILLANCE_WINDOW,
            'forecast_days'  => self::FORECAST_DAYS,
        ];
    }

    /**
     * Prevalência declarada de cada sintoma, em número e em percentagem do
     * total de declarações. "Não sabe" é contado à parte, porque não é uma
     * resposta negativa.
     *
     * @return array
     */
    private function symptomBreakdown()
    {
        $total = (int) $this->declarations()->count();

        $labels = [];
        $yes = [];
        $unsure = [];
        $items = [];

        foreach (array_keys(TravelerDeclaration::CLINICAL_QUESTIONS) as $question) {
            $yesCount = (int) $this->declarations()->where($question, 'YES')->count();
            $unsureCount = (int) $this->declarations()->where($question, 'UNSURE')->count();

            $label = isset($this->symptomShortLabels[$question])
                ? $this->symptomShortLabels[$question]
                : $question;

            $labels[] = $label;
            $yes[] = $yesCount;
            $unsure[] = $unsureCount;

            $items[] = [
                'label'  => $label,
                'yes'    => $yesCount,
                'unsure' => $unsureCount,
                'share'  => $total > 0 ? round($yesCount * 100 / $total, 1) : 0,
            ];
        }

        return [
            'labels' => $labels,
            'yes'    => $yes,
            'unsure' => $unsure,
            'items'  => $items,
        ];
    }

    /**
     * Duas séries por dia: declarações submetidas e chegadas efectivas.
     * A diferença entre elas mostra a antecedência com que os viajantes
     * preenchem o formulário.
     *
     * @param  int  $days
     * @return array
     */
    private function dailyMovement($days)
    {
        $start = Carbon::today()->subDays($days - 1)->startOfDay();

        $submitted = $this->declarations()
            ->where('submitted_at', '>=', $start)
            ->selectRaw('DATE(submitted_at) AS day, COUNT(*) AS total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $arrived = $this->declarations()
            ->where('arrival_date', '>=', $start->toDateString())
            ->where('arrival_date', '<=', Carbon::today()->toDateString())
            ->selectRaw('DATE(arrival_date) AS day, COUNT(*) AS total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $alerts = $this->alerts()
            ->where('submitted_at', '>=', $start)
            ->selectRaw('DATE(submitted_at) AS day, COUNT(*) AS total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $labels = [];
        $submittedValues = [];
        $arrivedValues = [];
        $alertValues = [];

        for ($i = 0; $i < $days; $i++) {
            $date = $start->copy()->addDays($i);
            $key = $date->format('Y-m-d');

            $labels[] = $date->format('d/m');
            $submittedValues[] = isset($submitted[$key]) ? (int) $submitted[$key] : 0;
            $arrivedValues[] = isset($arrived[$key]) ? (int) $arrived[$key] : 0;
            $alertValues[] = isset($alerts[$key]) ? (int) $alerts[$key] : 0;
        }

        return [
            'labels'    => $labels,
            'submitted' => $submittedValues,
            'arrived'   => $arrivedValues,
            'alerts'    => $alertValues,
        ];
    }

    /**
     * Chegadas previstas para os próximos dias, separando as que já vêm
     * sinalizadas. É a leitura que permite dimensionar as equipas de
     * vigilância no aeroporto.
     *
     * @param  int  $days
     * @return array
     */
    private function arrivalForecast($days)
    {
        $start = Carbon::today();

        $expected = $this->declarations()
            ->whereBetween('arrival_date', [
                $start->toDateString(),
                $start->copy()->addDays($days - 1)->toDateString(),
            ])
            ->selectRaw('DATE(arrival_date) AS day, COUNT(*) AS total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $flagged = $this->alerts()
            ->whereBetween('arrival_date', [
                $start->toDateString(),
                $start->copy()->addDays($days - 1)->toDateString(),
            ])
            ->selectRaw('DATE(arrival_date) AS day, COUNT(*) AS total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $labels = [];
        $expectedValues = [];
        $flaggedValues = [];

        for ($i = 0; $i < $days; $i++) {
            $date = $start->copy()->addDays($i);
            $key = $date->format('Y-m-d');

            $labels[] = $date->format('d/m');
            $expectedValues[] = isset($expected[$key]) ? (int) $expected[$key] : 0;
            $flaggedValues[] = isset($flagged[$key]) ? (int) $flagged[$key] : 0;
        }

        return [
            'labels'   => $labels,
            'expected' => $expectedValues,
            'flagged'  => $flaggedValues,
        ];
    }

    /**
     * Declarações por país de partida, com bandeira. É o país de embarque,
     * não a nacionalidade: para vigilância epidemiológica é a origem do voo
     * que importa.
     *
     * @return array
     */
    private function countryBreakdown()
    {
        $rows = $this->declarations()
            ->selectRaw("TRIM(COALESCE(NULLIF(departure_country, ''), '')) AS country_name, COUNT(*) AS total")
            ->groupBy('country_name')
            ->orderByDesc('total')
            ->orderBy('country_name')
            ->get();

        $alertRows = $this->alerts()
            ->selectRaw("TRIM(COALESCE(NULLIF(departure_country, ''), '')) AS country_name, COUNT(*) AS total")
            ->groupBy('country_name')
            ->pluck('total', 'country_name');

        $total = (int) $rows->sum('total');

        $countries = [];

        foreach ($rows as $row) {
            $name = trim((string) $row->country_name);

            if ($name === '') {
                $name = 'Não indicado';
            }

            $countries[] = [
                'name'     => $name,
                'total'    => (int) $row->total,
                'alerts'   => (int) ($alertRows[$row->country_name] ?? 0),
                'share'    => $total > 0 ? round($row->total * 100 / $total, 1) : 0,
                'flag'     => CountryFlag::url($name),
                'flag2x'   => CountryFlag::url($name, 2),
                'initials' => CountryFlag::initials($name),
            ];
        }

        return $countries;
    }

    /**
     * Declarações por motivo de viagem, já com o rótulo legível.
     *
     * @return array
     */
    private function purposeBreakdown()
    {
        $rows = $this->declarations()
            ->selectRaw('purpose_of_travel, COUNT(*) AS total')
            ->groupBy('purpose_of_travel')
            ->orderByDesc('total')
            ->get();

        $labels = [];
        $values = [];

        foreach ($rows as $row) {
            $labels[] = TravelerDeclaration::PURPOSES[$row->purpose_of_travel] ?? $row->purpose_of_travel;
            $values[] = (int) $row->total;
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    /**
     * Passagens declaradas por país sob vigilância. Como countries_visited é
     * um JSON com zero ou vários países, quem passou por dois conta nos dois.
     *
     * @return array
     */
    private function riskCountryBreakdown()
    {
        $items = [];
        $max = 0;

        foreach (TravelerDeclaration::RISK_COUNTRIES as $value => $label) {
            $count = (int) TravelerDeclaration::forRiskCountry($value)->count();
            $max = max($max, $count);

            $items[] = [
                'label' => $label,
                'total' => $count,
            ];
        }

        foreach ($items as $index => $item) {
            $items[$index]['width'] = $max > 0 ? round($item['total'] * 100 / $max) : 0;
        }

        // Maior primeiro, para o país mais crítico ficar no topo
        usort($items, function ($a, $b) {
            return $b['total'] - $a['total'];
        });

        return $items;
    }

    /**
     * Faixas etárias, calculadas na base de dados a partir da data de
     * nascimento. Relevante para as regras de vacinação e para a triagem
     * de menores.
     *
     * @return array
     */
    private function ageBreakdown()
    {
        $buckets = [
            '0-11'  => [0, 11],
            '12-17' => [12, 17],
            '18-29' => [18, 29],
            '30-44' => [30, 44],
            '45-59' => [45, 59],
            '60+'   => [60, 200],
        ];

        $labels = [];
        $values = [];

        foreach ($buckets as $label => $range) {
            $labels[] = $label . ' anos';
            $values[] = (int) $this->declarations()
                ->whereRaw('TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN ? AND ?', $range)
                ->count();
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    /**
     * Contagem agrupada por uma coluna de texto.
     *
     * @param  string  $column
     * @param  int  $limit
     * @return array
     */
    private function columnBreakdown($column, $limit = 10)
    {
        $rows = $this->declarations()
            ->selectRaw("TRIM(COALESCE(NULLIF({$column}, ''), 'Não indicado')) AS label, COUNT(*) AS total")
            ->groupBy('label')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();

        $max = (int) $rows->max('total');

        $items = [];

        foreach ($rows as $row) {
            $items[] = [
                'label' => $row->label,
                'total' => (int) $row->total,
                'width' => $max > 0 ? round($row->total * 100 / $max) : 0,
            ];
        }

        return $items;
    }

    /**
     * Voos com mais declarações sinalizadas, agrupados por voo e data de
     * chegada. É a lista de partida para o rastreio de contactos a bordo.
     *
     * @param  int  $limit
     * @return \Illuminate\Support\Collection
     */
    private function flightsWithAlerts($limit)
    {
        return $this->alerts()
            ->selectRaw(
                'airline_name,
                 airline_code,
                 flight_number,
                 arrival_date,
                 departure_country,
                 COUNT(*) AS total'
            )
            ->whereDate('arrival_date', '>=', Carbon::today()->subDays(self::SURVEILLANCE_WINDOW))
            ->groupBy('airline_name', 'airline_code', 'flight_number', 'arrival_date', 'departure_country')
            ->orderByDesc('total')
            ->orderByDesc('arrival_date')
            ->limit($limit)
            ->get();
    }
}
