@extends('layouts.merge.dashboard')
@section('titulo', 'Declaração de ' . $declaration->full_name)

@section('content')
    <div class="card mb-2">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="h4 page-title mb-0">
                        <b>
                            <a href="{{ route('admin.declaration.index') }}"><u>Declarações</u></a> >
                            {{ $declaration->full_name }}
                        </b>
                    </h2>
                </div>

                <div class="col-md-4 text-right">
                    @if (Auth::user()->level === 'Administrador')
                        <a href="{{ route('admin.declaration.edit', $declaration->id) }}"
                            class="btn btn-sm btn-secondary text-dark mt-2">
                            <i class="fa fa-pencil"></i> Editar
                        </a>
                    @endif

                    @if (Auth::user()->level === 'Visualizador' || Auth::user()->level === 'Administrador')
                        <a data-toggle="modal" data-target="#statusModal" class="btn btn-sm btn-warning text-dark mt-2">
                            <i class="fa fa-refresh text-dark"></i> Actualizar Estado
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @foreach (['status_updated' => 'Estado actualizado com sucesso.', 'edit' => 'Declaração actualizada com sucesso.'] as $key => $message)
        @if (session($key))
            <div class="alert alert-success mb-3">
                <i class="fa fa-check-circle"></i> {{ $message }}
            </div>
        @endif
    @endforeach

    {{-- Aviso operacional: substitui a leitura do questionário linha a linha --}}
    @if ($declaration->has_alert || $declaration->has_risk_exposure)
        <div class="alert alert-danger">
            <b><i class="fa fa-exclamation-triangle"></i> Declaração sujeita a triagem sanitária.</b>
            <ul class="mb-0 mt-2">
                @if ($declaration->has_alert)
                    <li>Existe pelo menos uma resposta afirmativa no questionário clínico.</li>
                @endif
                @if ($declaration->has_risk_exposure)
                    <li>Passagem declarada por país sob vigilância: {{ $declaration->visited_countries_label }}.</li>
                @endif
            </ul>
        </div>
    @endif

    <div class="card shadow">
        <div class="card-body">
            <div class="container-fluid">

                <div class="row justify-content-between mb-4 align-items-center">
                    <div class="col-12 col-md-2 text-center mt-3">
                        {{-- QR servido por rota autenticada, para conferir com o do viajante --}}
                        @if ($declaration->qr_path)
                            <a href="{{ route('admin.declaration.qrcode', $declaration->id) }}" target="_blank"
                                title="Ver QR code em tamanho real">
                                <img src="{{ route('admin.declaration.qrcode', $declaration->id) }}"
                                    alt="QR code da declaração" class="img-thumbnail"
                                    style="width: 130px; height: 130px; object-fit: contain;">
                            </a>
                        @else
                            <div class="border rounded d-flex align-items-center justify-content-center text-muted"
                                style="width: 130px; height: 130px; margin: 0 auto;">
                                <i class="fa fa-qrcode fa-2x"></i>
                            </div>
                        @endif
                    </div>

                    <div class="col-12 col-md-6 mt-3">
                        <h4 class="page-title"><b>{{ $declaration->full_name }}</b></h4>
                        <p class="text-muted mb-1">
                            Identificador da Declaração: <b class="text-primary">{{ $declaration->declaration_code }}</b>
                        </p>
                        @include('admin.declaration.partials._status', ['status' => $declaration->status])
                    </div>

                    <div class="col-12 col-md-4 text-right mt-3">
                        @if ($previousDeclarations->count())
                            <a href="{{ route('admin.declaration.traveler', $declaration->passport_number) }}"
                                class="btn btn-primary text-white btn-sm mb-2">
                                <i class="fa fa-history text-white"></i>
                                {{ $previousDeclarations->count() + 1 }} entradas deste passaporte
                            </a>
                        @else
                            <span class="badge badge-light border p-2">Primeira entrada declarada</span>
                        @endif
                    </div>
                </div>

                {{-- ============ IDENTIFICAÇÃO ============ --}}
                <h5 class="mx-3 mt-2 mb-3 text-primary"><b><i class="fa fa-user"></i> Identificação e Contactos</b></h5>

                @php
                    $model = \App\Models\TravelerDeclaration::class;

                    $identity = [
                        'Primeiro nome' => $declaration->first_name,
                        'Nome do meio' => $declaration->middle_name ?: '—',
                        'Apelido' => $declaration->last_name,
                        'Data de nascimento' => optional($declaration->date_of_birth)->format('d/m/Y'),
                        'Sexo' => $model::SEXES[$declaration->sex] ?? $declaration->sex,
                        'Nacionalidade' => $declaration->nationality,
                        'Passaporte' => $declaration->passport_number,
                        'Telefone' => $declaration->phone,
                        'E-mail' => $declaration->email,
                    ];

                    $travel = [
                        'Motivo da viagem' => $model::PURPOSES[$declaration->purpose_of_travel] ?? $declaration->purpose_of_travel,
                        'Companhia aérea' => $declaration->airline_name,
                        'Voo' => $declaration->flight_label,
                        'Lugar' => $declaration->seat_number,
                        'País de partida' => $declaration->departure_country,
                        'Cidade de partida' => $declaration->departure_city ?: '—',
                        'Data de chegada' => optional($declaration->arrival_date)->format('d/m/Y'),
                        'Local de estadia' => $declaration->place_of_stay ?: '—',
                        'Países visitados (21 dias)' => $declaration->visited_countries_label,
                    ];
                @endphp

                <div class="row mx-3 mb-4">
                    @foreach ($identity as $label => $value)
                        <div class="col-12 col-md-6 col-lg-4 mb-3">
                            <h5 class="mb-1 text-primary"><b>{{ $label }}</b></h5>
                            <p class="text-dark mb-0">{{ $value }}</p>
                        </div>
                    @endforeach
                </div>

                {{-- ============ VIAGEM ============ --}}
                <hr class="mx-3">
                <h5 class="mx-3 mt-4 mb-3 text-primary"><b><i class="fa fa-plane"></i> Dados da Viagem</b></h5>

                <div class="row mx-3 mb-4">
                    @foreach ($travel as $label => $value)
                        <div class="col-12 col-md-6 col-lg-4 mb-3">
                            <h5 class="mb-1 text-primary"><b>{{ $label }}</b></h5>
                            <p class="text-dark mb-0">{{ $value }}</p>
                        </div>
                    @endforeach
                </div>

                {{-- ============ DADOS CLÍNICOS ============ --}}
                <hr class="mx-3">
                <h5 class="mx-3 mt-4 mb-3 text-primary"><b><i class="fa fa-stethoscope"></i> Dados Clínicos</b></h5>

                <div class="mx-3 mb-4">
                    @include('admin.declaration.partials._clinical', ['declaration' => $declaration])
                </div>

                {{-- ============ ENTRADAS ANTERIORES ============ --}}
                @if ($previousDeclarations->count())
                    <hr class="mx-3">
                    <h5 class="mx-3 mt-4 mb-3 text-primary"><b><i class="fa fa-history"></i> Entradas Anteriores deste Passaporte</b></h5>

                    <div class="mx-3 mb-4 table-responsive">
                        <table class="table table-sm table-bordered table-hover mb-0">
                            <thead class="thead-light">
                                <tr class="text-center">
                                    <th>CÓDIGO</th>
                                    <th>CHEGADA</th>
                                    <th>VOO</th>
                                    <th>ORIGEM</th>
                                    <th>SINTOMAS</th>
                                    <th>ESTADO</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($previousDeclarations as $previous)
                                    <tr class="text-center text-dark">
                                        <td><small>{{ $previous->declaration_code }}</small></td>
                                        <td>{{ optional($previous->arrival_date)->format('d/m/Y') }}</td>
                                        <td>{{ $previous->flight_label }}</td>
                                        <td>{{ $previous->departure_country }}</td>
                                        <td>
                                            @if ($previous->has_alert)
                                                <span class="badge badge-danger p-2">COM SINTOMAS</span>
                                            @else
                                                <span class="badge badge-success p-2">SEM SINTOMAS</span>
                                            @endif
                                        </td>
                                        <td>@include('admin.declaration.partials._status', ['status' => $previous->status])</td>
                                        <td>
                                            <a href="{{ route('admin.declaration.show', $previous->id) }}"
                                                class="btn btn-sm btn-secondary text-dark">Ver</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                <div class="col-lg-12 mb-2">
                    <hr>
                    <p class="mb-1 text-dark">
                        <b>Declaração submetida em:</b>
                        {{ optional($declaration->submitted_at)->format('d/m/Y H:i') }}
                        <small class="text-muted">(IP {{ $declaration->submitted_ip }})</small>
                    </p>
                    <p class="mb-1 text-dark">
                        <b>Última Actualização:</b> {{ $declaration->updated_at->format('d/m/Y H:i') }}
                    </p>
                </div>

            </div>
        </div>
    </div>

    {{-- Modal de actualização de estado --}}
    <div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-refresh"></i> Actualizar Estado</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>

                <form action="{{ route('admin.declaration.status', $declaration->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p class="text-muted mb-3">
                            O estado será aplicado a <b>{{ $declaration->full_name }}
                                ({{ $declaration->declaration_code }})</b>.
                        </p>

                        <div class="form-group mb-0">
                            <label for="status">Estado</label>
                            <select name="status" id="status" class="form-control rounded border-secondary" required>
                                @foreach (['RECEIVED' => 'Recebida', 'ALERT' => 'Alerta', 'VERIFIED' => 'Verificada'] as $statusValue => $statusLabel)
                                    <option value="{{ $statusValue }}"
                                        {{ $declaration->status === $statusValue ? 'selected' : '' }}>
                                        {{ $statusLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary text-dark" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-sm btn-primary text-white">
                            <i class="fa fa-check text-white"></i> Guardar
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection
