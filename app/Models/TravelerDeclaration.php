<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TravelerDeclaration extends Model
{
    protected $fillable = [
        'declaration_code',
        'qr_path',
        // 1. Personal information
        'first_name',
        'middle_name',
        'last_name',
        'date_of_birth',
        'sex',
        'nationality',
        'passport_number',
        // 2. Contacts
        'phone',
        'email',
        // 3. Travel details
        // airline_code is derived server-side from the chosen airline,
        // it never comes from the form (see self::AIRLINES).
        'purpose_of_travel',
        'airline_name',
        'airline_code',
        'flight_number',
        'seat_number',
        'departure_country',
        'departure_city',
        'arrival_date',
        'place_of_stay',
        'countries_visited',
        // 4. Clinical data
        'evd_contact',
        'fever',
        'headache',
        'myalgia',
        'weakness',
        'bleeding',
        'other_symptoms',
        'other_symptoms_detail',
        // 5. Self-declaration
        'declaration_accepted',
        'submitted_at',
        'submitted_ip',
        'status'
    ];

    protected $casts = [
        'countries_visited'    => 'array',
        'declaration_accepted' => 'boolean',
        'date_of_birth'        => 'date',
        'arrival_date'         => 'date',
        'submitted_at'         => 'datetime',
        'otp_expires_at'       => 'datetime',
    ];

    /**
     * The OTP never leaves the server.
     */
    protected $hidden = ['otp_code'];

    /**
     * Answers accepted by the contact question and by the "other symptoms" question.
     */
    const YES_NO = ['YES', 'NO'];

    /**
     * Answers accepted by the symptom checklist.
     */
    const YES_NO_UNSURE = ['YES', 'NO', 'UNSURE'];

    /**
     * Answer labels shown on the form, the summary and the back office.
     */
    const ANSWER_LABELS = [
        'YES'    => 'Yes',
        'NO'     => 'No',
        'UNSURE' => 'Unsure',
    ];

    const SEXES = [
        'MALE'   => 'Male',
        'FEMALE' => 'Female',
    ];

    /**
     * Clinical questions, in the order they appear on the form.
     * Key = column, value = label used on the summary, the back office and the export.
     */
    const CLINICAL_QUESTIONS = [
        'evd_contact'    => 'Contact with a person showing Ebola Virus Disease (EVD) symptoms',
        'fever'          => 'Fever (or recent history of fever) (>= 38.5 °C / 101.3 °F)',
        'headache'       => 'Headache',
        'myalgia'        => 'Myalgia (muscle pain / body aches)',
        'weakness'       => 'Profound weakness or fatigue',
        'bleeding'       => 'Bloody diarrhoea, bleeding from gums, into the skin (purpura), eyes and urine',
        'other_symptoms' => 'Other symptom(s)',
    ];

    /**
     * Purpose of travel. Key is stored, value is shown on the form.
     */
    const PURPOSES = [
        'TOURISM'      => 'Tourism',
        'BUSINESS'     => 'Business',
        'WORK'         => 'Work',
        'STUDY'        => 'Study',
        'RELIGIOUS'    => 'Religious / Pilgrimage',
        'FAMILY_VISIT' => 'Family visit',
        'TRANSIT'      => 'Transit',
        'OFFICIAL'     => 'Diplomatic / Official mission',
        'MEDICAL'      => 'Medical treatment',
        'OTHER'        => 'Other',
    ];

    /**
     * Airlines and their IATA code, used as the prefix of the flight number.
     */
    const AIRLINES = [
        'TAAG ANGOLA AIRLINES' => 'DT',
        'ETHIOPIAN AIRLINES'   => 'ET',
        'EMIRATES'             => 'EK',
        'QATAR AIRWAYS'        => 'QR',
        'TAP AIR PORTUGAL'     => 'TP',
        'LUFTHANSA'            => 'LH',
        'AIR FRANCE'           => 'AF',
        'BRUSSELS AIRLINES'    => 'SN',
        'TURKISH AIRLINES'     => 'TK',
        'KENYA AIRWAYS'        => 'KQ',
        'RWANDAIR'             => 'WB',
        'SOUTH AFRICAN AIRWAYS' => 'SA',
        'OTHER'                => '',
    ];

    /**
     * Countries under active epidemiological surveillance, shown as checkboxes
     * on the travel step. Update this list when the MINSA reviews it.
     */
    const RISK_COUNTRIES = [
        'DR_CONGO'    => 'Congo, The Democratic Republic of the',
        'SOUTH_SUDAN' => 'South Sudan',
        'UGANDA'      => 'Uganda',
    ];

    /**
     * Generate a unique declaration code (e.g. MINSA26-4F7K2M-9QX1).
     */
    public static function generateDeclarationCode(): string
    {
        do {
            $code = 'MINSA26-' . strtoupper(Str::random(6)) . '-' . strtoupper(Str::random(4));
        } while (static::where('declaration_code', $code)->exists());

        return $code;
    }

    /**
     * Normalise a passport number so that the same document always yields the
     * same value: upper case, no spaces, hyphens or punctuation. Travellers
     * type "A 123-456" and "a123456" for the same passport.
     */
    public static function normalizePassport(?string $passport): string
    {
        return mb_strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $passport));
    }

    /**
     * Applied on every write, wherever it comes from: public form, back office
     * edit or seeder. This is what makes the traveller history reliable.
     */
    public function setPassportNumberAttribute($value): void
    {
        $this->attributes['passport_number'] = self::normalizePassport($value);
    }

    /**
     * Full name as declared, for the receipt, the e-mail and the back office.
     */
    public function getFullNameAttribute(): string
    {
        return trim(implode(' ', array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
        ])));
    }

    /**
     * Flight with the airline prefix, the way it appears on the boarding pass.
     * "ET 302"
     */
    public function getFlightLabelAttribute(): string
    {
        return trim($this->airline_code . ' ' . $this->flight_number);
    }

    /**
     * Risk countries as a readable line, for the receipt and the export.
     */
    public function getVisitedCountriesLabelAttribute(): string
    {
        $visited = $this->countries_visited ?? [];

        if (! $visited) {
            return 'None';
        }

        return implode(', ', array_map(function ($country) {
            return self::RISK_COUNTRIES[$country] ?? $country;
        }, $visited));
    }

    /**
     * Readable label for a stored answer (YES / NO / UNSURE).
     */
    public function answerLabel(string $question): string
    {
        return self::ANSWER_LABELS[$this->$question] ?? (string) $this->$question;
    }

    /**
     * Whether any clinical question was answered "YES".
     * Drives the alert badge in the back office.
     */
    public function getHasAlertAttribute(): bool
    {
        foreach (array_keys(self::CLINICAL_QUESTIONS) as $question) {
            if ($this->$question === 'YES') {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether the traveller declared a stay in a country under surveillance.
     */
    public function getHasRiskExposureAttribute(): bool
    {
        return ! empty($this->countries_visited);
    }

    /**
     * Status resolved from the answers, applied on submit.
     * A declaration already checked at the port of entry keeps VERIFIED.
     */
    public function resolveStatus(): string
    {
        if ($this->status === 'VERIFIED') {
            return 'VERIFIED';
        }

        return ($this->has_alert || $this->has_risk_exposure) ? 'ALERT' : 'RECEIVED';
    }

    /**
     * Every declaration ever submitted with this passport, newest arrival first,
     * including the current one.
     */
    public function declarationHistory()
    {
        return static::samePassport($this->passport_number)->orderByDesc('arrival_date');
    }

    /**
     * The other declarations submitted with this passport. Shown as a block on
     * the details page, so the officer sees the traveller's previous entries
     * without having to search for them.
     */
    public function previousDeclarations()
    {
        return $this->declarationHistory()->where('id', '!=', $this->id);
    }

    /**
     * Filter every declaration of a given passport, normalising the number
     * first so that a differently typed passport still matches.
     * Example: TravelerDeclaration::samePassport('a 123-456')->get()
     */
    public function scopeSamePassport($query, ?string $passport)
    {
        return $query->where('passport_number', self::normalizePassport($passport));
    }

    /**
     * Filter the declarations that need sanitary screening.
     * Example: TravelerDeclaration::withAlerts()->get()
     */
    public function scopeWithAlerts($query)
    {
        return $query->where(function ($q) {
            foreach (array_keys(self::CLINICAL_QUESTIONS) as $question) {
                $q->orWhere($question, 'YES');
            }
        });
    }

    /**
     * Filter by a country visited in the last 21 days. Works on MySQL 8 JSON columns.
     * Example: TravelerDeclaration::forRiskCountry('UGANDA')->get()
     */
    public function scopeForRiskCountry($query, string $country)
    {
        return $query->whereJsonContains('countries_visited', $country);
    }

    /**
     * Filter the arrivals of a given day, for the airport screening list.
     */
    public function scopeArrivingOn($query, $date)
    {
        return $query->whereDate('arrival_date', $date);
    }

    /**
     * Resolve route model binding by declaration code.
     */
    public function getRouteKeyName(): string
    {
        return 'declaration_code';
    }
}
