@extends('layouts.merge.dashboard')
@section('titulo', 'Editar Declaração ' . $declaration->full_name)

@section('content')
    <div class="card mb-2">
        <div class="card-body">
            <h2 class="h4 page-title mb-0">
                <a href="{{ route('admin.declaration.index') }}"><u>Declarações</u></a> >
                <a href="{{ route('admin.declaration.show', $declaration->id) }}"><u>{{ $declaration->full_name }}</u></a>
                > Editar
            </h2>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @php
                $model = \App\Models\TravelerDeclaration::class;

                // Campos de texto simples: rótulo, nome da coluna e tipo de input
                $textFields = [
                    ['label' => 'Primeiro nome', 'name' => 'first_name', 'type' => 'text', 'required' => true],
                    ['label' => 'Nome do meio', 'name' => 'middle_name', 'type' => 'text', 'required' => false],
                    ['label' => 'Apelido', 'name' => 'last_name', 'type' => 'text', 'required' => true],
                    ['label' => 'Data de nascimento', 'name' => 'date_of_birth', 'type' => 'date', 'required' => true],
                    ['label' => 'Número do passaporte', 'name' => 'passport_number', 'type' => 'text', 'required' => true],
                    ['label' => 'Telefone', 'name' => 'phone', 'type' => 'text', 'required' => true],
                    ['label' => 'E-mail', 'name' => 'email', 'type' => 'email', 'required' => true],
                    ['label' => 'Número do voo', 'name' => 'flight_number', 'type' => 'text', 'required' => true],
                    ['label' => 'Número do lugar', 'name' => 'seat_number', 'type' => 'text', 'required' => true],
                    ['label' => 'Cidade de partida', 'name' => 'departure_city', 'type' => 'text', 'required' => false],
                    ['label' => 'Data de chegada', 'name' => 'arrival_date', 'type' => 'date', 'required' => true],
                    ['label' => 'Local de estadia', 'name' => 'place_of_stay', 'type' => 'text', 'required' => false],
                ];

                // As datas vêm como Carbon: o input date precisa de Y-m-d
                $currentValue = function ($field) use ($declaration) {
                    $value = $declaration->$field;

                    return $value instanceof \Carbon\Carbon ? $value->format('Y-m-d') : $value;
                };

                $currentCountries = (array) old('countries_visited', $declaration->countries_visited ?? []);
            @endphp

            <form action="{{ route('admin.declaration.update', $declaration->id) }}" method="POST" class="mx-4">
                @csrf
                @method('PUT')

                <h4 class="my-4">
                    <b>{{ $declaration->declaration_code }} : {{ $declaration->full_name }}</b>
                    <hr>
                </h4>

                {{-- ============ IDENTIFICAÇÃO E VIAGEM ============ --}}
                <div class="row mx-2">

                    @foreach ($textFields as $field)
                        <div class="col-12 col-md-6 col-lg-4 mt-3">
                            <div class="form-group">
                                <label for="{{ $field['name'] }}">{{ $field['label'] }}</label>
                                <input type="{{ $field['type'] }}" name="{{ $field['name'] }}"
                                    id="{{ $field['name'] }}" class="form-control"
                                    value="{{ old($field['name'], $currentValue($field['name'])) }}"
                                    {{ $field['required'] ? 'required' : '' }}>
                            </div>
                        </div>
                    @endforeach

                    <div class="col-12 col-md-6 col-lg-4 mt-3">
                        <div class="form-group">
                            <label for="sex">Sexo</label>
                            <select name="sex" id="sex" class="form-control" required>
                                @foreach ($model::SEXES as $sexValue => $sexLabel)
                                    <option value="{{ $sexValue }}"
                                        {{ old('sex', $declaration->sex) === $sexValue ? 'selected' : '' }}>
                                        {{ $sexLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-4 mt-3">
                        <div class="form-group">
                            <label for="nationality">Nacionalidade</label>
                            <select name="nationality" id="nationality" class="form-control" required>
                                @foreach (config('countries') as $code => $countryName)
                                    <option value="{{ $countryName }}"
                                        {{ old('nationality', $declaration->nationality) === $countryName ? 'selected' : '' }}>
                                        {{ $countryName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-4 mt-3">
                        <div class="form-group">
                            <label for="departure_country">País de partida</label>
                            <select name="departure_country" id="departure_country" class="form-control" required>
                                @foreach (config('countries') as $code => $countryName)
                                    <option value="{{ $countryName }}"
                                        {{ old('departure_country', $declaration->departure_country) === $countryName ? 'selected' : '' }}>
                                        {{ $countryName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-4 mt-3">
                        <div class="form-group">
                            <label for="purpose_of_travel">Motivo da viagem</label>
                            <select name="purpose_of_travel" id="purpose_of_travel" class="form-control" required>
                                @foreach ($model::PURPOSES as $purposeValue => $purposeLabel)
                                    <option value="{{ $purposeValue }}"
                                        {{ old('purpose_of_travel', $declaration->purpose_of_travel) === $purposeValue ? 'selected' : '' }}>
                                        {{ $purposeLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- O código IATA é deduzido da companhia e não se preenche aqui --}}
                    <div class="col-12 col-md-6 col-lg-4 mt-3">
                        <div class="form-group">
                            <label for="airline_name">Companhia aérea</label>
                            <select name="airline_name" id="airline_name" class="form-control" required>
                                @foreach ($model::AIRLINES as $airlineName => $iataCode)
                                    <option value="{{ $airlineName }}"
                                        {{ old('airline_name', $declaration->airline_name) === $airlineName ? 'selected' : '' }}>
                                        {{ $airlineName }} {{ $iataCode ? '(' . $iataCode . ')' : '' }}
                                    </option>
                                @endforeach

                                {{-- Companhia retirada da lista depois desta declaração --}}
                                @unless (array_key_exists($declaration->airline_name, $model::AIRLINES))
                                    <option value="{{ $declaration->airline_name }}" selected>
                                        {{ $declaration->airline_name }} (valor anterior)
                                    </option>
                                @endunless
                            </select>
                        </div>
                    </div>

                    <div class="col-12 mt-3">
                        <div class="form-group">
                            <label>Países visitados nos últimos 21 dias</label>
                            <div>
                                @foreach ($model::RISK_COUNTRIES as $countryValue => $countryLabel)
                                    <div class="form-check form-check-inline mr-4">
                                        <input class="form-check-input" type="checkbox" name="countries_visited[]"
                                            id="country{{ $loop->index }}" value="{{ $countryValue }}"
                                            {{ in_array($countryValue, $currentCountries, true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="country{{ $loop->index }}">
                                            {{ $countryLabel }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                </div>

                {{-- ============ DADOS CLÍNICOS ============ --}}
                <hr class="mt-4">
                <h5 class="text-primary mb-3"><b>Dados Clínicos</b></h5>

                @php
                    $answerLabels = ['YES' => 'Sim', 'NO' => 'Não', 'UNSURE' => 'Não sabe'];
                    $yesNoOnly = ['evd_contact', 'other_symptoms'];
                @endphp

                <div class="row mx-2">
                    @foreach ($model::CLINICAL_QUESTIONS as $field => $label)
                        @php
                            // As duas perguntas Sim/Não não aceitam "Não sabe"
                            $options = in_array($field, $yesNoOnly, true)
                                ? \Illuminate\Support\Arr::only($answerLabels, ['YES', 'NO'])
                                : $answerLabels;
                        @endphp
                        <div class="col-12 col-md-6 mt-3">
                            <div class="form-group">
                                <label for="{{ $field }}">{{ $label }}</label>
                                <select name="{{ $field }}" id="{{ $field }}" class="form-control" required>
                                    @foreach ($options as $answerValue => $answerLabel)
                                        <option value="{{ $answerValue }}"
                                            {{ old($field, $declaration->$field) === $answerValue ? 'selected' : '' }}>
                                            {{ $answerLabel }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endforeach

                    <div class="col-12 col-md-6 mt-3">
                        <div class="form-group">
                            <label for="other_symptoms_detail">
                                Descrição dos outros sintomas
                                <small class="text-muted">(obrigatório se a resposta anterior for Sim)</small>
                            </label>
                            <input type="text" name="other_symptoms_detail" id="other_symptoms_detail"
                                class="form-control"
                                value="{{ old('other_symptoms_detail', $declaration->other_symptoms_detail) }}">
                        </div>
                    </div>

                    <div class="col-12 col-md-6 mt-3">
                        <div class="form-group">
                            <label for="status">Estado da triagem</label>
                            <select name="status" id="status" class="form-control border-dark" required>
                                @php
                                    $statusLabels = ['RECEIVED' => 'Recebida', 'ALERT' => 'Alerta', 'VERIFIED' => 'Verificada'];
                                @endphp
                                @foreach ($statuses as $statusOption)
                                    <option value="{{ $statusOption }}"
                                        {{ old('status', $declaration->status) === $statusOption ? 'selected' : '' }}>
                                        {{ $statusLabels[$statusOption] ?? $statusOption }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- O QR code e o identificador não são editáveis: o viajante já os tem --}}
                <div class="col-md-12 mt-4">
                    <div class="alert alert-light border">
                        <i class="fa fa-info-circle"></i>
                        O identificador <b>{{ $declaration->declaration_code }}</b> e o QR code associado não são
                        alterados por esta edição, para que o documento já entregue pelo viajante continue válido.
                    </div>
                </div>

                <div class="col-md-12 mt-3">
                    <div class="form-group text-center">
                        <button type="submit" class="btn px-5 col-md-4 btn-primary">
                            Salvar alterações
                            <span class="fe fe-chevron-right fe-16"></span>
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
@endsection
