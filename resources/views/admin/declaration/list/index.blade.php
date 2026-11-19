@extends('layouts.merge.dashboard')
@section('titulo', 'Declarações de Saúde do Viajante')

@section('content')
    <div class="card mb-2">
        <div class="card-body">
            <div class="row justify-content-between align-items-center">
                <div class="col-12 col-md-6">
                    <h2 class="h4 page-title mb-0">
                        <b>Declarações de Saúde do Viajante</b>
                    </h2>
                </div>

                <div class="col-12 col-md-6 text-md-right mt-2 mt-md-0">
                    <a href="{{ route('admin.declaration.travelers') }}" class="btn btn-sm btn-secondary text-dark">
                        <i class="fa fa-users"></i> Por viajante
                    </a>

                    @if (Auth::user()->level === 'Técnico' || Auth::user()->level === 'Administrador')
                        <a data-toggle="modal" data-target="#declarationModal" class="btn btn-sm btn-primary text-white">
                            <i class="fa fa-file-pdf-o text-white"></i> Exportar/Imprimir
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ============ INDICADORES ============ --}}
    <div class="row mb-2">
        @php
            $indicators = [
                ['label' => 'Com alerta sanitário', 'value' => $declarations_alert->count(), 'class' => 'text-danger'],
                ['label' => 'Sem alerta', 'value' => $declarations->count(), 'class' => 'text-dark'],
                ['label' => 'Chegadas hoje', 'value' => $declarations_alert->merge($declarations)->where('arrival_date', '>=', today())->where('arrival_date', '<', today()->addDay())->count(), 'class' => 'text-primary'],
                ['label' => 'Viajantes distintos', 'value' => $declarations_alert->merge($declarations)->pluck('passport_number')->unique()->count(), 'class' => 'text-dark'],
            ];
        @endphp

        @foreach ($indicators as $indicator)
            <div class="col-6 col-lg-3 mb-2">
                <div class="card h-100">
                    <div class="card-body py-3">
                        <small class="text-muted d-block">{{ $indicator['label'] }}</small>
                        <h3 class="mb-0 {{ $indicator['class'] }}"><b>{{ $indicator['value'] }}</b></h3>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ============ COM ALERTA SANITÁRIO ============ --}}
    <div class="card mb-4">
        <div class="card-body">
            <h4 class="text-center text-danger mb-3">
                <i class="fa fa-exclamation-triangle"></i> Declarações com Alerta Sanitário
            </h4>
            <p class="text-center text-muted small">
                Pelo menos uma resposta afirmativa no questionário clínico ou passagem por país sob vigilância.
                Encaminhar para a equipa de vigilância no ponto de entrada.
            </p>

            @include('admin.declaration.partials._table', [
                'items' => $declarations_alert,
                'tableId' => 'dataTable-1',
                'counts' => $declarationCounts,
            ])
        </div>
    </div>

    {{-- ============ RESTANTES DECLARAÇÕES ============ --}}
    <div class="card">
        <div class="card-body">
            <h4 class="text-center mb-3">Declarações Recebidas</h4>

            @include('admin.declaration.partials._table', [
                'items' => $declarations,
                'tableId' => 'dataTable-2',
                'counts' => $declarationCounts,
            ])
        </div>
    </div>

    @include('extra._modal.declarationsReport.index')
@endsection

@section('JS')
    <script>
        $(function() {
            // Alertas ordenados pela chegada mais próxima, o resto pelo registo mais recente
          
            $('#dataTable-2').DataTable({
                autoWidth: true,
                lengthMenu: [
                    [8, 16, 32, -1],
                    [8, 16, 32, 'All']
                ],
                order: [
                    [0, 'desc']
                ]
            });
        });
    </script>
@endsection
