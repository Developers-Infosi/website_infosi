<?php

namespace App\Http\Controllers\Admin;

use App\Classes\Logger;
use App\Classes\Random;
use App\Http\Controllers\Controller;
use App\Models\TravelerDeclaration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TravelerDeclarationController extends Controller
{
    /**
     * Screening statuses. Only three are valid.
     */
    private const STATUSES = ['RECEIVED', 'ALERT', 'VERIFIED'];

    /*
     * Purposes, airlines, sexes and the countries under surveillance are not
     * listed here. They live in the TravelerDeclaration model constants, so
     * the public form, this controller and the edit view can never drift apart.
     */

    private $Logger;
    private $random;

    public function __construct()
    {
        $this->Logger = new Logger();
        $this->random = new Random();
    }

    /**
     * Two lists, the same way the participants are split by status:
     * declarations needing sanitary screening first, cleared ones after.
     *
     * One line per declaration, not per traveller: this is the arrivals list
     * used at the port of entry. The repeat counter tells the officer when the
     * same passport has declared before, and links to the traveller history.
     */
    public function index()
    {
        $response['declarations_alert'] = TravelerDeclaration::where('status', 'ALERT')
            ->orderBy('arrival_date', 'asc')
            ->get();

        $response['declarations'] = TravelerDeclaration::whereIn(
            'status', ['RECEIVED', 'VERIFIED']
        )->orderBy('id', 'desc')->get();

        // passport_number => how many declarations that passport has submitted.
        // One grouped query instead of one count per row.
        $response['declarationCounts'] = $this->declarationCountsFor(
            $response['declarations_alert']->merge($response['declarations'])
        );

        //Logger
        $this->Logger->log('emergency', 'Listou as Declarações de Saúde do Viajante');

        return view('admin.declaration.list.index', $response);
    }

    /**
     * One line per traveller instead of one per declaration: passport, name,
     * number of entries, last arrival and whether any of them raised an alert.
     * This is the epidemiological surveillance view, it does not replace the
     * arrivals list.
     */
    public function travelers(Request $request)
    {
        $search = trim((string) $request->input('search'));

        /*
         * Aggregate per passport. MAX(id) gives the most recent declaration,
         * which is then loaded to show the name and nationality as last
         * declared: the traveller may have corrected them in the meantime.
         */
        $aggregates = TravelerDeclaration::selectRaw(
                'passport_number,
                 MAX(id) as last_declaration_id,
                 COUNT(*) as total_declarations,
                 MAX(arrival_date) as last_arrival_date,
                 MIN(arrival_date) as first_arrival_date,
                 SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as alert_count',
                ['ALERT']
            )
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('passport_number', 'like', '%' . TravelerDeclaration::normalizePassport($search) . '%')
                        ->orWhere('first_name', 'like', '%' . $search . '%')
                        ->orWhere('last_name', 'like', '%' . $search . '%');
                });
            })
            ->groupBy('passport_number')
            ->orderByDesc('last_arrival_date')
            ->get();

        $latest = TravelerDeclaration::whereIn('id', $aggregates->pluck('last_declaration_id'))
            ->get()
            ->keyBy('id');

        $response['travelers'] = $aggregates;
        $response['latest'] = $latest;
        $response['search'] = $search;

        //Logger
        $this->Logger->log('emergency', 'Listou os viajantes agrupados por passaporte');

        return view('admin.declaration.travelers.index', $response);
    }

    /**
     * Every declaration submitted with one passport, newest arrival first.
     *
     * @param  string  $passport
     * @return \Illuminate\Http\Response
     */
    public function traveler($passport)
    {
        $declarations = TravelerDeclaration::samePassport($passport)
            ->orderByDesc('arrival_date')
            ->get();

        abort_if($declarations->isEmpty(), 404);

        //Logger
        $this->Logger->log(
            'emergency',
            'Consultou o histórico do passaporte ' . TravelerDeclaration::normalizePassport($passport)
        );

        return view('admin.declaration.travelers.details', [
            'declarations' => $declarations,
            'traveler'     => $declarations->first(),
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $declaration = TravelerDeclaration::findOrFail($id);

        //Logger
        $this->Logger->log(
            'emergency',
            'Visualizou a declaração com o identificador ' . $id
        );

        return view('admin.declaration.details.index', [
            'declaration' => $declaration,
            // Other entries of the same passport, shown under the declaration
            'previousDeclarations' => $declaration->previousDeclarations()->get(),
        ]);
    }

    /**
     * Serve the declaration QR code inline, so it can be shown in the details
     * page and checked against the code presented by the traveller.
     *
     * The file lives on the public disk under qrcodes/, but it is served
     * through this authenticated route so the back office never depends on
     * the storage symlink.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function qrcode($id)
    {
        $declaration = TravelerDeclaration::findOrFail($id);

        abort_if(
            empty($declaration->qr_path) ||
            ! Storage::disk('public')->exists($declaration->qr_path),
            404
        );

        //Logger
        $this->Logger->log(
            'emergency',
            'Visualizou o QR code da declaração ' . $id
        );

        return Storage::disk('public')->response(
            $declaration->qr_path,
            'QR_' . $declaration->declaration_code . '.svg',
            [
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * The edit view reads the option lists straight from the model constants,
     * so no purpose or airline list is passed here.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $response['declaration'] = TravelerDeclaration::findOrFail($id);
        $response['statuses'] = self::STATUSES;

        //Logger
        $this->Logger->log(
            'emergency',
            'Entrou em editar a declaração com o identificador ' . $id
        );

        return view('admin.declaration.edit.index', $response);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $declaration = TravelerDeclaration::findOrFail($id);

        /*
         * Airlines and risk countries are reviewed over time. The value already
         * stored is allowed through so older declarations stay editable, even
         * when the option was meanwhile removed from the list.
         */
        $allowedAirlines = array_values(array_unique(array_merge(
            array_keys(TravelerDeclaration::AIRLINES),
            array_filter([$declaration->airline_name])
        )));

        $allowedCountriesVisited = array_values(array_unique(array_merge(
            array_keys(TravelerDeclaration::RISK_COUNTRIES),
            (array) $declaration->countries_visited
        )));

        // The symptom checklist also accepts Unsure, the two Yes/No questions do not
        $symptomRule = ['required', Rule::in(TravelerDeclaration::YES_NO_UNSURE)];

        $validated = $request->validate([
            // 1. Personal information
            'first_name'      => 'required|string|max:100',
            'middle_name'     => 'nullable|string|max:100',
            'last_name'       => 'required|string|max:100',
            'date_of_birth'   => 'required|date|before:today',
            'sex'             => ['required', Rule::in(array_keys(TravelerDeclaration::SEXES))],
            'nationality'     => 'required|string|max:100',
            'passport_number' => 'required|string|max:50',

            // 2. Contacts
            'phone' => 'required|string|max:30',
            'email' => 'required|email|max:255',

            // 3. Travel details
            'purpose_of_travel'   => ['required', Rule::in(array_keys(TravelerDeclaration::PURPOSES))],
            'airline_name'        => ['required', Rule::in($allowedAirlines)],
            'flight_number'       => 'required|string|max:20',
            'seat_number'         => 'required|string|max:10',
            'departure_country'   => 'required|string|max:100',
            'departure_city'      => 'nullable|string|max:100',
            'arrival_date'        => 'required|date',
            'place_of_stay'       => 'nullable|string|max:255',
            'countries_visited'   => 'nullable|array',
            'countries_visited.*' => [Rule::in($allowedCountriesVisited)],

            // 4. Clinical data
            'evd_contact'           => ['required', Rule::in(TravelerDeclaration::YES_NO)],
            'fever'                 => $symptomRule,
            'headache'              => $symptomRule,
            'myalgia'               => $symptomRule,
            'weakness'              => $symptomRule,
            'bleeding'              => $symptomRule,
            'other_symptoms'        => ['required', Rule::in(TravelerDeclaration::YES_NO)],
            'other_symptoms_detail' => 'required_if:other_symptoms,YES|nullable|string|max:255',

            'status' => ['required', Rule::in(self::STATUSES)],
        ], [
            'countries_visited.*.in'            => 'Um dos países seleccionados não é válido.',
            'other_symptoms_detail.required_if' => 'Descreva os outros sintomas indicados.',
        ]);

        // The IATA prefix is never submitted: it is derived from the chosen airline
        $validated['airline_code'] = TravelerDeclaration::AIRLINES[$validated['airline_name']] ?? $declaration->airline_code;

        // An unchecked checkbox group is absent from the request, not empty
        $validated['countries_visited'] = $request->input('countries_visited', []);

        // Ensure an empty field is stored as NULL, not as an empty string
        $validated['departure_city']        = $validated['departure_city'] ?: null;
        $validated['place_of_stay']         = $validated['place_of_stay'] ?: null;
        $validated['other_symptoms_detail'] = $validated['other_symptoms'] === 'YES'
            ? $validated['other_symptoms_detail']
            : null;

        // The passport is normalised by the model mutator, so an edit never
        // splits the traveller history in two.
        $declaration->update($validated);

        //Logger
        $this->Logger->log('emergency', 'Editou a declaração com o identificador ' . $id);

        return redirect()->route('admin.declaration.show', $id)->with('edit', '1');
    }


    /**
     * Update only the screening status.
     */
    public function Alterstatus(Request $request, $id)
    {
        $request->validate([
            'status' => ['required', Rule::in(self::STATUSES)],
        ]);

        $declaration = TravelerDeclaration::findOrFail($id);
        $declaration->status = $request->status;
        $declaration->save();

        //Logger
        $this->Logger->log('info', 'Status da Declaração ID ' . $id . ' actualizado para ' . $request->status);

        return redirect()->route('admin.declaration.show', $id)->with('status_updated', '1');
    }

    /**
     * How many declarations each of the listed passports has submitted.
     * Returns [passport_number => total], so the list view can flag repeat
     * travellers without running a count per row.
     */
    private function declarationCountsFor($declarations): array
    {
        $passports = $declarations->pluck('passport_number')->unique()->all();

        if (! $passports) {
            return [];
        }

        return TravelerDeclaration::select('passport_number', DB::raw('COUNT(*) as total'))
            ->whereIn('passport_number', $passports)
            ->groupBy('passport_number')
            ->pluck('total', 'passport_number')
            ->all();
    }
}
