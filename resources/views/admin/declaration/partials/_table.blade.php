{{-- Declarations table. Used by both lists on the index, so the columns are
     defined once. Expects: $items, $tableId and optionally $counts. --}}
<div class="table-responsive">
    <table class="table datatables table-hover table-bordered table-striped" id="{{ $tableId }}">
        <thead class="bg-primary thead-dark">
            <tr class="text-center">
                <th>#</th>
                <th>CÓDIGO</th>
                <th>VIAJANTE</th>
                <th>PASSAPORTE</th>
                <th>NACIONALIDADE</th>
                <th>VOO</th>
                <th>ORIGEM</th>
                <th>CHEGADA</th>
                <th>SINTOMAS</th>
                <th>PAÍSES DE RISCO</th>
            </tr>
        </thead>
        <tbody class="bg-white">
            @forelse ($items as $item)
                <tr class="text-center text-dark {{ $item->has_alert ? 'table-danger' : '' }}">
                    <td>{{ $item->id }}</td>
                    <td><small>{{ $item->declaration_code }}</small></td>
                    {{-- Nome e estado em linhas próprias: nomes longos não empurram
                         os badges para cima do texto --}}
                    <td class="text-left" style="min-width:220px;">
                        <a href="{{ route('admin.declaration.show', $item->id) }}"
                            class="text-decoration-none text-dark d-block mb-1">
                            <b>{{ $item->full_name }}</b>
                        </a>

                        <div class="d-flex flex-wrap align-items-center" style="gap:4px;">
                            @include('admin.declaration.partials._status', ['status' => $item->status])

                            {{-- Repeat traveller: links to every entry of this passport --}}
                            @if (($counts[$item->passport_number] ?? 1) > 1)
                                <a href="{{ route('admin.declaration.traveler', $item->passport_number) }}"
                                    class="badge badge-info d-inline-block px-2 py-1">
                                    {{ $counts[$item->passport_number] }} entradas
                                </a>
                            @endif
                        </div>
                    </td>
                    <td>{{ $item->passport_number }}</td>
                    <td>{{ $item->nationality }}</td>
                    <td>{{ $item->flight_label }} <br><small class="text-muted">Lugar {{ $item->seat_number }}</small></td>
                    <td>
                        {{ $item->departure_country }}
                        @if ($item->departure_city)
                            <br><small class="text-muted">{{ $item->departure_city }}</small>
                        @endif
                    </td>
                    <td>{{ optional($item->arrival_date)->format('d/m/Y') }}</td>
                    <td>
                        @if ($item->has_alert)
                            <span class="badge badge-danger p-2">COM SINTOMAS</span>
                        @else
                            <span class="badge badge-success p-2">SEM SINTOMAS</span>
                        @endif
                    </td>
                    <td>
                        @forelse ($item->countries_visited ?? [] as $country)
                            <span class="badge badge-warning">
                                {{ \App\Models\TravelerDeclaration::RISK_COUNTRIES[$country] ?? $country }}
                            </span>
                        @empty
                            <span class="text-muted">—</span>
                        @endforelse
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center text-muted py-4">Sem declarações nesta lista.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
