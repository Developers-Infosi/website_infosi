{{-- Guardar como: resources/views/admin/home/index.blade.php --}}
@extends('layouts.merge.dashboard')
@section('titulo', 'Painel de Vigilância Sanitária')
@section('content')

    @if (in_array(Auth::user()->level, ['Administrador', 'Técnico']))

        <style>
            .stat-card {
                border: 0;
                border-radius: .5rem;
                height: 100%;
            }

            .stat-value {
                font-size: 1.6rem;
                font-weight: 700;
                line-height: 1.1;
            }

            .stat-label {
                font-size: .72rem;
                text-transform: uppercase;
                letter-spacing: .05em;
                color: #6c757d;
            }

            .stat-hint {
                font-size: .72rem;
                color: #98a2b3;
            }

            .scope-tag {
                display: inline-block;
                font-size: .65rem;
                text-transform: uppercase;
                letter-spacing: .04em;
                padding: .1rem .4rem;
                border-radius: 3px;
                background: #e8f3ec;
                color: #1aa053;
                font-weight: 600;
            }

            .scope-tag.alert {
                background: #fdecec;
                color: #c0392b;
            }

            .scope-tag.pending {
                background: #fdf3e0;
                color: #b47500;
            }

            .chart-box {
                position: relative;
                width: 100%;
                height: 260px;
            }

            .chart-box-sm {
                height: 230px;
            }

            .chart-box-lg {
                height: 320px;
            }

            .country-scroll {
                max-height: 420px;
                overflow-y: auto;
            }

            .country-scroll::-webkit-scrollbar {
                width: 6px;
            }

            .country-scroll::-webkit-scrollbar-thumb {
                background: #d8dce3;
                border-radius: 3px;
            }

            .country-row {
                display: flex;
                align-items: center;
                padding: .5rem .25rem;
                border-bottom: 1px solid #f1f2f4;
            }

            .country-row:last-child {
                border-bottom: 0;
            }

            .country-flag {
                width: 24px;
                height: 18px;
                object-fit: cover;
                border-radius: 2px;
                box-shadow: 0 0 0 1px rgba(0, 0, 0, .1);
            }

            .country-initials {
                width: 24px;
                height: 18px;
                border-radius: 2px;
                background: #eef0f3;
                color: #6c757d;
                font-size: .6rem;
                font-weight: 700;
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }

            .country-name {
                font-size: .85rem;
                font-weight: 500;
            }

            .mini-bar {
                height: 4px;
                background: #eef0f3;
                border-radius: 2px;
                overflow: hidden;
                margin-top: .25rem;
            }

            .mini-bar span {
                display: block;
                height: 100%;
                background: #1aa053;
            }

            .mini-bar span.alert {
                background: #c0392b;
            }

            .rank-list {
                list-style: none;
                padding: 0;
                margin: 0;
            }

            .rank-list li {
                padding: .45rem 0;
                border-bottom: 1px solid #f1f2f4;
            }

            .rank-list li:last-child {
                border-bottom: 0;
            }

            .rank-label {
                font-size: .82rem;
            }

            .empty-state {
                color: #98a2b3;
                font-size: .85rem;
                padding: 2rem 0;
                text-align: center;
            }

            .alert-strip {
                border-left: 4px solid #c0392b;
                background: #fdf6f5;
                border-radius: .35rem;
                padding: .75rem 1rem;
            }
        </style>

        {{-- Cabeçalho: define a leitura antes de mostrar números --}}
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="card rounded shadow-sm px-3">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center py-3">
                        <div>
                            <h6 class="mb-1"><b>Declaração de Saúde do Viajante</b></h6>
                            <span class="stat-hint">
                                Declaração é cada submissão do formulário. Viajante é um passaporte distinto.
                                Alerta é a declaração com sintoma referido ou passagem por país sob vigilância.
                            </span>
                        </div>
                        <span class="stat-hint text-md-right">
                            Janela de vigilância: {{ $counters['window'] }} dias
                            <br>{{ $counters['arriving_today'] }} chegadas hoje &middot;
                            {{ $counters['arriving_week'] }} nos próximos {{ $counters['forecast_days'] }} dias
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Aviso operacional: só aparece quando há trabalho por fazer --}}
        @if ($counters['alerts_ahead'] > 0)
            <div class="row">
                <div class="col-md-12 grid-margin">
                    <div class="alert-strip">
                        <b class="text-danger">{{ $counters['alerts_ahead'] }}</b>
                        {{ $counters['alerts_ahead'] == 1 ? 'declaração sinalizada com chegada prevista' : 'declarações sinalizadas com chegada prevista' }}.
                        <a href="{{ route('admin.declaration.index') }}" class="ml-1">Ver lista de triagem</a>
                    </div>
                </div>
            </div>
        @endif

        {{-- ============ CONTADORES ============ --}}
        <div class="row">
            <div class="col-6 col-md-3 grid-margin stretch-card">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <div class="stat-label">Declarações</div>
                        <div class="stat-value text-success">{{ number_format($counters['total'], 0, ',', ' ') }}</div>
                        <div class="stat-hint">
                            {{ number_format($counters['travelers'], 0, ',', ' ') }} viajantes distintos
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3 grid-margin stretch-card">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <div class="stat-label">Sujeitas a triagem</div>
                        <div class="stat-value text-danger">{{ $counters['alerts'] }}</div>
                        <div class="stat-hint">
                            {{ $counters['alert_rate'] }}% do total &middot; {{ $counters['pending'] }} por verificar
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3 grid-margin stretch-card">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <div class="stat-label">Países de origem</div>
                        <div class="stat-value">{{ $counters['countries'] }}</div>
                        <div class="stat-hint">países de embarque declarados</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3 grid-margin stretch-card">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <div class="stat-label">Submissões hoje</div>
                        <div class="stat-value">{{ $counters['today'] }}</div>
                        <div class="stat-hint">{{ $counters['last7'] }} nos últimos 7 dias</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Prevalência de sintomas: a leitura sanitária central --}}
            <div class="col-lg-8 grid-margin">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h4 class="card-title mb-1">Sintomas declarados</h4>
                                <span class="scope-tag alert">Respostas afirmativas</span>
                            </div>
                            <span class="stat-hint text-right">
                                {{ $counters['symptomatic'] }} com sintomas
                                <br>{{ $counters['risk_exposed'] }} vindos de país sob vigilância
                            </span>
                        </div>

                        @if ($counters['total'] > 0)
                            <div class="chart-box chart-box-lg mt-3">
                                <canvas id="symptomsChart"></canvas>
                            </div>
                            <p class="stat-hint mt-2 mb-0">
                                A barra clara representa quem respondeu "não sabe", que para efeitos de triagem não
                                equivale a uma resposta negativa.
                            </p>
                        @else
                            <div class="empty-state">Ainda não há declarações submetidas.</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Países de origem --}}
            <div class="col-lg-4 grid-margin">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h4 class="card-title mb-1">Declarações por país de origem</h4>
                        <span class="scope-tag">País de embarque</span>
                        <p class="stat-hint mt-2 mb-2">A barra vermelha assinala a proporção sinalizada para triagem.</p>

                        @if (count($countries) > 0)
                            <input type="text" id="countrySearch" class="form-control form-control-sm mb-2"
                                placeholder="Procurar país...">

                            <div class="country-scroll" id="countryList">
                                @foreach ($countries as $country)
                                    <div class="country-row" data-country="{{ mb_strtolower($country['name']) }}">
                                        <div class="mr-2">
                                            @if ($country['flag'])
                                                <img class="country-flag" src="{{ $country['flag'] }}"
                                                    srcset="{{ $country['flag2x'] }} 2x" alt="{{ $country['name'] }}"
                                                    loading="lazy">
                                            @else
                                                <span class="country-initials">{{ $country['initials'] }}</span>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1 mr-2" style="min-width:0;">
                                            <div class="country-name text-truncate" title="{{ $country['name'] }}">
                                                {{ $country['name'] }}
                                            </div>
                                            <div class="mini-bar">
                                                <span class="{{ $country['alerts'] > 0 ? 'alert' : '' }}"
                                                    style="width: {{ $country['share'] }}%"></span>
                                            </div>
                                        </div>
                                        <div class="text-right" style="min-width:56px;">
                                            <b>{{ $country['total'] }}</b>
                                            <div class="stat-hint">
                                                @if ($country['alerts'] > 0)
                                                    <span class="text-danger">{{ $country['alerts'] }} alerta(s)</span>
                                                @else
                                                    {{ $country['share'] }}%
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="stat-hint mt-2" id="countryEmpty" style="display:none;">
                                Nenhum país corresponde à procura.
                            </div>
                        @else
                            <div class="empty-state">Ainda não há declarações submetidas.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Evolução: submissões, chegadas e alertas --}}
            <div class="col-lg-8 grid-margin">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h4 class="card-title mb-1">Movimento diário</h4>
                        <span class="scope-tag pending">Últimos 14 dias</span>
                        <p class="stat-hint mt-2 mb-0">
                            A distância entre a linha de submissões e a de chegadas mostra a antecedência com que o
                            formulário é preenchido.
                        </p>
                        <div class="chart-box mt-2">
                            <canvas id="dailyChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Chegadas previstas --}}
            <div class="col-lg-4 grid-margin">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h4 class="card-title mb-1">Chegadas previstas</h4>
                        <span class="scope-tag pending">Próximos {{ $counters['forecast_days'] }} dias</span>
                        <p class="stat-hint mt-2 mb-0">
                            Dimensionamento das equipas de vigilância no ponto de entrada.
                        </p>
                        <div class="chart-box chart-box-sm mt-2">
                            <canvas id="forecastChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Motivo da viagem --}}
            <div class="col-lg-4 grid-margin stretch-card">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h4 class="card-title mb-1">Motivo da viagem</h4>
                        <span class="scope-tag">Todas as declarações</span>

                        @if (count($purposes['labels']) > 0)
                            <div class="chart-box chart-box-sm mt-3">
                                <canvas id="purposeChart"></canvas>
                            </div>
                        @else
                            <div class="empty-state">Sem dados.</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Faixas etárias --}}
            <div class="col-lg-4 grid-margin stretch-card">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h4 class="card-title mb-1">Faixa etária</h4>
                        <span class="scope-tag">Todas as declarações</span>
                        <p class="stat-hint mt-2 mb-0">
                            Relevante para as regras de vacinação e para o acompanhamento de menores.
                        </p>
                        <div class="chart-box chart-box-sm mt-2">
                            <canvas id="ageChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Países sob vigilância --}}
            <div class="col-lg-4 grid-margin stretch-card">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h4 class="card-title mb-1">Passagem por país sob vigilância</h4>
                        <span class="scope-tag alert">Últimos {{ $counters['window'] }} dias antes da viagem</span>

                        @if (count($riskCountries) > 0)
                            <ul class="rank-list mt-3">
                                @foreach ($riskCountries as $item)
                                    <li>
                                        <div class="d-flex justify-content-between">
                                            <span class="rank-label text-truncate"
                                                title="{{ $item['label'] }}">{{ $item['label'] }}</span>
                                            <b class="ml-2">{{ $item['total'] }}</b>
                                        </div>
                                        <div class="mini-bar"><span class="alert" style="width: {{ $item['width'] }}%"></span></div>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="empty-state">Nenhum país sob vigilância configurado.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Companhias aéreas --}}
            <div class="col-lg-5 grid-margin stretch-card">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h4 class="card-title mb-1">Companhias aéreas</h4>
                        <span class="scope-tag">Todas as declarações</span>

                        @if (count($airlines) > 0)
                            <ul class="rank-list mt-3">
                                @foreach ($airlines as $item)
                                    <li>
                                        <div class="d-flex justify-content-between">
                                            <span class="rank-label text-truncate"
                                                title="{{ $item['label'] }}">{{ $item['label'] }}</span>
                                            <b class="ml-2">{{ $item['total'] }}</b>
                                        </div>
                                        <div class="mini-bar"><span style="width: {{ $item['width'] }}%"></span></div>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="empty-state">Sem dados de companhias aéreas.</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Voos com alertas: ponto de partida do rastreio de contactos --}}
            <div class="col-lg-7 grid-margin stretch-card">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h4 class="card-title mb-1">Voos com declarações sinalizadas</h4>
                        <span class="scope-tag alert">Últimos {{ $counters['window'] }} dias</span>
                        <p class="stat-hint mt-2 mb-2">
                            Base para o rastreio de contactos a bordo.
                        </p>

                        @if ($alertFlights->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead>
                                        <tr class="text-muted" style="font-size:.72rem;text-transform:uppercase;">
                                            <th>Voo</th>
                                            <th>Companhia</th>
                                            <th>Origem</th>
                                            <th>Chegada</th>
                                            <th class="text-right">Sinalizados</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($alertFlights as $flight)
                                            <tr>
                                                <td><b>{{ trim($flight->airline_code . ' ' . $flight->flight_number) }}</b></td>
                                                <td class="text-truncate" style="max-width:160px;">{{ $flight->airline_name }}</td>
                                                <td class="text-truncate" style="max-width:140px;">{{ $flight->departure_country }}</td>
                                                <td>{{ \Carbon\Carbon::parse($flight->arrival_date)->format('d/m/Y') }}</td>
                                                <td class="text-right">
                                                    <span class="badge badge-danger p-2">{{ $flight->total }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="empty-state">Nenhum voo com declarações sinalizadas no período.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
        <script>
            (function() {
                var green = '#1aa053';
                var amber = '#f0a202';
                var red = '#c0392b';
                var grey = '#c0c6cf';
                var gridColor = 'rgba(0,0,0,.06)';

                var symptomLabels = @json($symptoms['labels']);
                var symptomYes = @json($symptoms['yes']);
                var symptomUnsure = @json($symptoms['unsure']);
                var dailyLabels = @json($daily['labels']);
                var dailySubmitted = @json($daily['submitted']);
                var dailyArrived = @json($daily['arrived']);
                var dailyAlerts = @json($daily['alerts']);
                var forecastLabels = @json($forecast['labels']);
                var forecastExpected = @json($forecast['expected']);
                var forecastFlagged = @json($forecast['flagged']);
                var purposeLabels = @json($purposes['labels']);
                var purposeValues = @json($purposes['values']);
                var ageLabels = @json($ageGroups['labels']);
                var ageValues = @json($ageGroups['values']);

                // Sintomas: barras horizontais empilhadas, "sim" primeiro
                var symptomsCanvas = document.getElementById('symptomsChart');

                if (symptomsCanvas && symptomLabels.length) {
                    new Chart(symptomsCanvas.getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: symptomLabels,
                            datasets: [{
                                    label: 'Sim',
                                    data: symptomYes,
                                    backgroundColor: red,
                                    borderRadius: 4,
                                    maxBarThickness: 22
                                },
                                {
                                    label: 'Não sabe',
                                    data: symptomUnsure,
                                    backgroundColor: amber,
                                    borderRadius: 4,
                                    maxBarThickness: 22
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            indexAxis: 'y',
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        boxWidth: 12
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    stacked: true,
                                    beginAtZero: true,
                                    ticks: {
                                        precision: 0
                                    },
                                    grid: {
                                        color: gridColor
                                    }
                                },
                                y: {
                                    stacked: true,
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        autoSkip: false,
                                        callback: function(value) {
                                            var label = this.getLabelForValue(value);
                                            return label.length > 34 ? label.substring(0, 34) + '...' : label;
                                        }
                                    }
                                }
                            }
                        }
                    });
                }

                var dailyCanvas = document.getElementById('dailyChart');

                if (dailyCanvas) {
                    new Chart(dailyCanvas.getContext('2d'), {
                        type: 'line',
                        data: {
                            labels: dailyLabels,
                            datasets: [{
                                    label: 'Declarações submetidas',
                                    data: dailySubmitted,
                                    borderColor: amber,
                                    backgroundColor: 'rgba(240,162,2,.12)',
                                    borderWidth: 2,
                                    fill: true,
                                    tension: .3,
                                    pointRadius: 3
                                },
                                {
                                    label: 'Chegadas',
                                    data: dailyArrived,
                                    borderColor: green,
                                    backgroundColor: 'rgba(26,160,83,.12)',
                                    borderWidth: 2,
                                    fill: true,
                                    tension: .3,
                                    pointRadius: 3
                                },
                                {
                                    label: 'Sinalizadas para triagem',
                                    data: dailyAlerts,
                                    borderColor: red,
                                    backgroundColor: 'rgba(192,57,43,.10)',
                                    borderWidth: 2,
                                    fill: false,
                                    tension: .3,
                                    pointRadius: 3
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        boxWidth: 12
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        precision: 0
                                    },
                                    grid: {
                                        color: gridColor
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    }
                                }
                            }
                        }
                    });
                }

                // Previsão: barras empilhadas, o vermelho é subconjunto do total
                var forecastCanvas = document.getElementById('forecastChart');

                if (forecastCanvas) {
                    new Chart(forecastCanvas.getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: forecastLabels,
                            datasets: [{
                                    label: 'Sinalizadas',
                                    data: forecastFlagged,
                                    backgroundColor: red,
                                    borderRadius: 4,
                                    maxBarThickness: 26
                                },
                                {
                                    label: 'Restantes',
                                    data: forecastExpected.map(function(total, index) {
                                        return Math.max(0, total - forecastFlagged[index]);
                                    }),
                                    backgroundColor: green,
                                    borderRadius: 4,
                                    maxBarThickness: 26
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        boxWidth: 12
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    stacked: true,
                                    grid: {
                                        display: false
                                    }
                                },
                                y: {
                                    stacked: true,
                                    beginAtZero: true,
                                    ticks: {
                                        precision: 0
                                    },
                                    grid: {
                                        color: gridColor
                                    }
                                }
                            }
                        }
                    });
                }

                var purposeCanvas = document.getElementById('purposeChart');

                if (purposeCanvas && purposeLabels.length) {
                    new Chart(purposeCanvas.getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: purposeLabels,
                            datasets: [{
                                data: purposeValues,
                                backgroundColor: [green, amber, '#2d7dd2', '#7b4bc0', red, '#17a398', '#e07b39',
                                    grey, '#5c6b73', '#96a13a'
                                ],
                                borderWidth: 0
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '58%',
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        boxWidth: 10,
                                        font: {
                                            size: 10
                                        }
                                    }
                                }
                            }
                        }
                    });
                }

                var ageCanvas = document.getElementById('ageChart');

                if (ageCanvas) {
                    new Chart(ageCanvas.getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: ageLabels,
                            datasets: [{
                                label: 'Declarações',
                                data: ageValues,
                                backgroundColor: '#2d7dd2',
                                borderRadius: 4,
                                maxBarThickness: 28
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        precision: 0
                                    },
                                    grid: {
                                        color: gridColor
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    }
                                }
                            }
                        }
                    });
                }

                var search = document.getElementById('countrySearch');

                if (search) {
                    search.addEventListener('input', function() {
                        var term = this.value.toLowerCase().trim();
                        var rows = document.querySelectorAll('#countryList .country-row');
                        var visible = 0;

                        for (var i = 0; i < rows.length; i++) {
                            var match = rows[i].getAttribute('data-country').indexOf(term) !== -1;
                            rows[i].style.display = match ? '' : 'none';
                            if (match) {
                                visible++;
                            }
                        }

                        document.getElementById('countryEmpty').style.display = visible ? 'none' : 'block';
                    });
                }
            })();
        </script>

    @endif
@endsection
