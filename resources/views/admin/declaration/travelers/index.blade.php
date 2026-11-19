@extends('layouts.merge.dashboard')
@section('titulo', 'Viajantes')

@section('content')
    <div class="card mb-2">
        <div class="card-body">
            <div class="row justify-content-between align-items-center">
                <div class="col-12 col-md-6">
                    <h2 class="h4 page-title mb-0">
                        <b>
                            <a href="{{ route('admin.declaration.index') }}"><u>Declarações</u></a> > Por viajante
                        </b>
                    </h2>
                </div>

                <div class="col-12 col-md-6 mt-2 mt-md-0">
                    {{-- Uma linha por passaporte, com todas as entradas agrupadas --}}
                    <form method="GET" action="{{ route('admin.declaration.travelers') }}" class="form-inline justify-content-md-end">
                        <input type="text" name="search" class="form-control form-control-sm mr-2"
                            placeholder="Nome ou passaporte" value="{{ $search }}">
                        <button type="submit" class="btn btn-sm btn-primary text-white">
                            <i class="fa fa-search text-white"></i> Pesquisar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table datatables table-hover table-bordered table-striped" id="dataTable-travelers">
                <thead class="bg-primary thead-dark">
                    <tr class="text-center">
                        <th>PASSAPORTE</th>
                        <th>VIAJANTE</th>
                        <th>NACIONALIDADE</th>
                        <th>ENTRADAS</th>
                        <th>PRIMEIRA</th>
                        <th>ÚLTIMA</th>
                        <th>ALERTAS</th>
                        <th>ACÇÕES</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    @forelse ($travelers as $row)
                        @php $last = $latest[$row->last_declaration_id] ?? null; @endphp
                        <tr class="text-center text-dark {{ $row->alert_count > 0 ? 'table-danger' : '' }}">
                            <td>{{ $row->passport_number }}</td>
                            <td class="text-left">
                                <a href="{{ route('admin.declaration.traveler', $row->passport_number) }}"
                                    class="text-decoration-none text-dark">
                                    <b>{{ $last ? $last->full_name : '—' }}</b>
                                </a>
                                {{-- Nome tal como foi declarado da última vez --}}
                            </td>
                            <td>{{ $last ? $last->nationality : '—' }}</td>
                            <td><span class="badge badge-info p-2">{{ $row->total_declarations }}</span></td>
                            <td>{{ \Carbon\Carbon::parse($row->first_arrival_date)->format('d/m/Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($row->last_arrival_date)->format('d/m/Y') }}</td>
                            <td>
                                @if ($row->alert_count > 0)
                                    <span class="badge badge-danger p-2">{{ $row->alert_count }}</span>
                                @else
                                    <span class="badge badge-success p-2">0</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.declaration.traveler', $row->passport_number) }}"
                                    class="btn btn-sm btn-secondary text-dark">
                                    <i class="fa fa-list"></i> Histórico
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Nenhum viajante encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('JS')
    <script>
        $(function() {
            $('#dataTable-travelers').DataTable({
                autoWidth: true,
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, 'All']
                ],
                order: [
                    [5, 'desc']
                ]
            });
        });
    </script>
@endsection
