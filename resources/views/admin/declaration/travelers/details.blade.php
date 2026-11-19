@extends('layouts.merge.dashboard')
@section('titulo', 'Histórico de ' . $traveler->full_name)

@section('content')
    <div class="card mb-2">
        <div class="card-body">
            <h2 class="h4 page-title mb-0">
                <b>
                    <a href="{{ route('admin.declaration.index') }}"><u>Declarações</u></a> >
                    <a href="{{ route('admin.declaration.travelers') }}"><u>Por viajante</u></a> >
                    {{ $traveler->full_name }}
                </b>
            </h2>
        </div>
    </div>

    {{-- Ficha do viajante, com os dados tal como foram declarados na última entrada --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="row mx-2">
                <div class="col-12 col-md-6 col-lg-3 mb-3">
                    <h5 class="mb-1 text-primary"><b>Passaporte</b></h5>
                    <p class="text-dark mb-0">{{ $traveler->passport_number }}</p>
                </div>
                <div class="col-12 col-md-6 col-lg-3 mb-3">
                    <h5 class="mb-1 text-primary"><b>Data de nascimento</b></h5>
                    <p class="text-dark mb-0">{{ optional($traveler->date_of_birth)->format('d/m/Y') }}</p>
                </div>
                <div class="col-12 col-md-6 col-lg-3 mb-3">
                    <h5 class="mb-1 text-primary"><b>Nacionalidade</b></h5>
                    <p class="text-dark mb-0">{{ $traveler->nationality }}</p>
                </div>
                <div class="col-12 col-md-6 col-lg-3 mb-3">
                    <h5 class="mb-1 text-primary"><b>Total de entradas declaradas</b></h5>
                    <p class="text-dark mb-0"><b>{{ $declarations->count() }}</b></p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-responsive">
            <h4 class="text-center mb-3">Histórico de Declarações</h4>

            <table class="table table-hover table-bordered table-striped">
                <thead class="bg-primary thead-dark">
                    <tr class="text-center">
                        <th>CÓDIGO</th>
                        <th>CHEGADA</th>
                        <th>VOO</th>
                        <th>ORIGEM</th>
                        <th>MOTIVO</th>
                        <th>PAÍSES DE RISCO</th>
                        <th>SINTOMAS</th>
                        <th>ESTADO</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    @foreach ($declarations as $item)
                        <tr class="text-center text-dark {{ $item->has_alert ? 'table-danger' : '' }}">
                            <td><small>{{ $item->declaration_code }}</small></td>
                            <td>{{ optional($item->arrival_date)->format('d/m/Y') }}</td>
                            <td>{{ $item->flight_label }}</td>
                            <td>{{ $item->departure_country }}</td>
                            <td><small>{{ \App\Models\TravelerDeclaration::PURPOSES[$item->purpose_of_travel] ?? $item->purpose_of_travel }}</small></td>
                            <td>
                                @forelse ($item->countries_visited ?? [] as $country)
                                    <span class="badge badge-warning">
                                        {{ \App\Models\TravelerDeclaration::RISK_COUNTRIES[$country] ?? $country }}
                                    </span>
                                @empty
                                    <span class="text-muted">—</span>
                                @endforelse
                            </td>
                            <td>
                                @if ($item->has_alert)
                                    <span class="badge badge-danger p-2">COM SINTOMAS</span>
                                @else
                                    <span class="badge badge-success p-2">SEM SINTOMAS</span>
                                @endif
                            </td>
                            <td>@include('admin.declaration.partials._status', ['status' => $item->status])</td>
                            <td>
                                <a href="{{ route('admin.declaration.show', $item->id) }}"
                                    class="btn btn-sm btn-secondary text-dark">Ver</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
