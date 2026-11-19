<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TravelerDeclaration;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DeclarationReportController extends Controller
{
    /**
     * Every column the operator may pick, in canonical output order.
     * Key = database column, value = label shown in the report.
     *
     * The seven clinical questions are offered individually, so the health
     * team can build a symptom-only listing without the travel data.
     */
    private const AVAILABLE_COLUMNS = [
        'declaration_code'      => 'Identificador da Declaração',
        'full_name'             => 'Nome Completo',
        'sex'                   => 'Sexo',
        'date_of_birth'         => 'Data de Nascimento',
        'nationality'           => 'Nacionalidade',
        'passport_number'       => 'N.º de Passaporte',
        'phone'                 => 'Telefone',
        'email'                 => 'Email',
        'purpose_of_travel'     => 'Motivo da Viagem',
        'airline_name'          => 'Companhia Aérea',
        'flight_label'          => 'Voo',
        'seat_number'           => 'Lugar',
        'departure_country'     => 'País de Partida',
        'departure_city'        => 'Cidade de Partida',
        'arrival_date'          => 'Data de Chegada',
        'place_of_stay'         => 'Local de Estadia',
        'countries_visited'     => 'Países Sob Vigilância Visitados',
        'evd_contact'           => 'Contacto com Caso Suspeito (DVE)',
        'fever'                 => 'Febre',
        'headache'              => 'Cefaleia',
        'myalgia'               => 'Mialgia',
        'weakness'              => 'Fraqueza ou Fadiga',
        'bleeding'              => 'Manifestações Hemorrágicas',
        'other_symptoms'        => 'Outros Sintomas',
        'other_symptoms_detail' => 'Descrição dos Outros Sintomas',
        'status'                => 'Estado da Triagem',
        'submitted_at'          => 'Data de Submissão',
    ];

    private const DEFAULT_COLUMNS = [
        'declaration_code', 'full_name', 'nationality', 'passport_number',
        'flight_label', 'departure_country', 'arrival_date', 'status',
    ];

    private const DATE_COLUMNS = [
        'date_of_birth', 'arrival_date', 'submitted_at',
    ];

    /**
     * Columns holding a clinical answer (YES / NO / UNSURE).
     */
    private const ANSWER_COLUMNS = [
        'evd_contact', 'fever', 'headache', 'myalgia', 'weakness',
        'bleeding', 'other_symptoms',
    ];

    /**
     * Screening statuses, with the label used in the report header.
     */
    private const STATUS_LABELS = [
        'RECEIVED' => 'Recebida',
        'ALERT'    => 'Alerta',
        'VERIFIED' => 'Verificada',
    ];

    /**
     * Expose the column list to the Blade modal without duplicating it there.
     */
    public static function availableColumns(): array
    {
        return self::AVAILABLE_COLUMNS;
    }

    public static function defaultColumns(): array
    {
        return self::DEFAULT_COLUMNS;
    }

    /**
     * Filter options for the modal.
     */
    public static function statusOptions(): array
    {
        return self::STATUS_LABELS;
    }

    public static function airlineOptions(): array
    {
        return array_keys(TravelerDeclaration::AIRLINES);
    }

    public static function riskCountryOptions(): array
    {
        return TravelerDeclaration::RISK_COUNTRIES;
    }

    /* ---------------------------------------------------------------------
     | Public endpoints
     |-------------------------------------------------------------------- */

    /**
     * Declarations report as PDF (A4 landscape).
     */
    public function pdf(Request $request)
    {
        $this->validateFilters($request);

        $columns      = $this->selectedColumns($request);
        $declarations = $this->buildQuery($request)->get();

        $pdf = PDF::loadView('pdf.reports.declarations', [
            'columns'      => $columns,
            'declarations' => $declarations,
            'filters'      => $this->filterSummary($request),
            'generatedAt'  => now()->format('d/m/Y H:i'),
            'formatter'    => fn ($declaration, $key) => $this->formatValue($declaration, $key),
        ]);

        $pdf->setPaper('A4', 'landscape');

        $this->writeLog('Imprimiu a lista de Declarações de Saúde do Viajante (PDF)');

        return $pdf->stream('Declaracoes de Saude do Viajante.pdf');
    }

    /**
     * Declarations report as Excel (.xls), streamed to the browser.
     */
    public function excel(Request $request): StreamedResponse
    {
        $this->validateFilters($request);

        $columns      = $this->selectedColumns($request);
        $declarations = $this->buildQuery($request)->get();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Declarações');

        // Header row
        $columnIndex = 1;
        foreach ($columns as $label) {
            $sheet->setCellValueByColumnAndRow($columnIndex++, 1, $label);
        }

        // Data rows — everything as text so passport numbers and flight codes
        // keep leading zeros
        $rowIndex = 2;
        foreach ($declarations as $declaration) {
            $columnIndex = 1;
            foreach (array_keys($columns) as $key) {
                $sheet->setCellValueExplicitByColumnAndRow(
                    $columnIndex++,
                    $rowIndex,
                    $this->formatValue($declaration, $key),
                    DataType::TYPE_STRING
                );
            }
            $rowIndex++;
        }

        $this->styleSheet($sheet);

        $fileName = 'Declaracoes_de_Saude_do_Viajante_' . now()->format('Y-m-d_His') . '.xls';

        $this->writeLog('Exportou a lista de Declarações de Saúde do Viajante (Excel)');

        return new StreamedResponse(function () use ($spreadsheet) {
            (new Xls($spreadsheet))->save('php://output');
        }, 200, [
            'Content-Type'        => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Cache-Control'       => 'max-age=0',
            'Pragma'              => 'public',
        ]);
    }

    /* ---------------------------------------------------------------------
     | Internals
     |-------------------------------------------------------------------- */

    private function validateFilters(Request $request): void
    {
        $request->validate([
            'status'    => 'required|in:' . implode(',', array_keys(self::STATUS_LABELS)) . ',TODAS',
            'screening' => 'required|in:COM_ALERTA,SEM_ALERTA,TODAS',

            // Optional: absent means no filtering
            'airline_name' => 'nullable|string|in:' . implode(',', self::airlineOptions()),
            'risk_country' => 'nullable|string|in:' . implode(',', array_keys(TravelerDeclaration::RISK_COUNTRIES)),
            'arrival_from' => 'nullable|date',
            'arrival_to'   => 'nullable|date|after_or_equal:arrival_from',

            'columns'   => 'nullable|array',
            'columns.*' => 'string|in:' . implode(',', array_keys(self::AVAILABLE_COLUMNS)),
        ]);
    }

    private function buildQuery(Request $request): Builder
    {
        $query = TravelerDeclaration::query();

        if ($request->status && $request->status !== 'TODAS') {
            $query->where('status', $request->status);
        }

        /*
         * Screening is not the same as status: a traveller with no symptoms
         * coming from a country under surveillance still needs screening, and
         * a declaration may carry a symptom before anyone has changed its
         * status. Both cases are covered here.
         */
        if ($request->screening === 'COM_ALERTA') {
            $query->where(function ($sub) {
                $sub->withAlerts()->orWhere(function ($risk) {
                    $risk->whereNotNull('countries_visited')->where('countries_visited', '<>', '[]');
                });
            });
        } elseif ($request->screening === 'SEM_ALERTA') {
            foreach (array_keys(TravelerDeclaration::CLINICAL_QUESTIONS) as $question) {
                $query->where($question, '<>', 'YES');
            }

            $query->where(function ($sub) {
                $sub->whereNull('countries_visited')->orWhere('countries_visited', '[]');
            });
        }

        if ($request->filled('airline_name')) {
            $query->where('airline_name', $request->airline_name);
        }

        // Risk countries live in a JSON column: MySQL 8 handles this natively
        if ($request->filled('risk_country')) {
            $query->whereJsonContains('countries_visited', $request->risk_country);
        }

        if ($request->filled('arrival_from')) {
            $query->whereDate('arrival_date', '>=', $request->arrival_from);
        }

        if ($request->filled('arrival_to')) {
            $query->whereDate('arrival_date', '<=', $request->arrival_to);
        }

        // Arrival order: this is an operational list for the point of entry
        return $query->orderBy('arrival_date', 'ASC')->orderBy('last_name', 'ASC');
    }

    /**
     * Whitelist the requested columns and force the canonical order.
     */
    private function selectedColumns(Request $request): array
    {
        $requested = (array) $request->input('columns', []);

        $columns = array_filter(
            self::AVAILABLE_COLUMNS,
            fn ($key) => in_array($key, $requested, true),
            ARRAY_FILTER_USE_KEY
        );

        if (empty($columns)) {
            $columns = array_intersect_key(
                self::AVAILABLE_COLUMNS,
                array_flip(self::DEFAULT_COLUMNS)
            );
        }

        return $columns;
    }

    private function formatValue(TravelerDeclaration $declaration, string $key): string
    {
        // full_name and flight_label are accessors, not columns
        $value = $declaration->{$key};

        // countries_visited is cast to array by the model
        if (is_array($value)) {
            if (empty($value)) {
                return '—';
            }

            return implode(' | ', array_map(
                fn ($country) => TravelerDeclaration::RISK_COUNTRIES[$country] ?? $country,
                $value
            ));
        }

        if (in_array($key, self::ANSWER_COLUMNS, true)) {
            $answers = ['YES' => 'Sim', 'NO' => 'Não', 'UNSURE' => 'Não sabe'];

            return $answers[$value] ?? '—';
        }

        if ($key === 'sex') {
            return TravelerDeclaration::SEXES[$value] ?? '—';
        }

        if ($key === 'purpose_of_travel') {
            return TravelerDeclaration::PURPOSES[$value] ?? '—';
        }

        if ($key === 'status') {
            return self::STATUS_LABELS[$value] ?? (string) $value;
        }

        if (in_array($key, self::DATE_COLUMNS, true)) {
            $format = $key === 'submitted_at' ? 'd/m/Y H:i' : 'd/m/Y';

            return $value ? Carbon::parse($value)->format($format) : '—';
        }

        return ($value === null || $value === '') ? '—' : (string) $value;
    }

    private function filterSummary(Request $request): array
    {
        return [
            'status'    => $request->status === 'TODAS'
                ? 'Todos'
                : (self::STATUS_LABELS[$request->status] ?? $request->status),
            'screening' => match ($request->screening) {
                'COM_ALERTA' => 'Apenas com sintomas ou exposição a país sob vigilância',
                'SEM_ALERTA' => 'Apenas sem sintomas e sem exposição',
                default      => 'Todas',
            },
            'airline'   => $request->filled('airline_name') ? $request->airline_name : 'Todas',
            'risk'      => $request->filled('risk_country')
                ? (TravelerDeclaration::RISK_COUNTRIES[$request->risk_country] ?? $request->risk_country)
                : 'Indiferente',
            'period'    => $this->periodSummary($request),
        ];
    }

    private function periodSummary(Request $request): string
    {
        $from = $request->filled('arrival_from') ? Carbon::parse($request->arrival_from)->format('d/m/Y') : null;
        $to = $request->filled('arrival_to') ? Carbon::parse($request->arrival_to)->format('d/m/Y') : null;

        if ($from && $to) {
            return $from . ' a ' . $to;
        }

        if ($from) {
            return 'a partir de ' . $from;
        }

        if ($to) {
            return 'até ' . $to;
        }

        return 'Todo o período';
    }

    private function styleSheet($sheet): void
    {
        $lastColumn = $sheet->getHighestColumn();
        $lastRow    = $sheet->getHighestRow();

        $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E79']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(22);

        $sheet->getStyle("A1:{$lastColumn}{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['rgb' => 'BFBFBF'],
                ],
            ],
        ]);

        foreach (range('A', $lastColumn) as $letter) {
            $sheet->getColumnDimension($letter)->setAutoSize(true);
        }

        $sheet->freezePane('A2');
        $sheet->setAutoFilter("A1:{$lastColumn}1");
    }

    /**
     * The Logger service is resolved here so this controller stays independent
     * of TravelerDeclarationController. Adjust to match your existing binding.
     */
    private function writeLog(string $message): void
    {
        if (property_exists($this, 'Logger') && $this->Logger) {
            $this->Logger->log('emergency', $message);
        }
    }
}
