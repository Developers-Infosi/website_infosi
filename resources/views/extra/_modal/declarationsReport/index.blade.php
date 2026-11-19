<form class="card-body" method="POST" action="{{ route('admin.declaration.report.pdf') }}" target="_blank">
    @csrf
    <div class="modal fade" id="declarationModal" tabindex="-1" role="dialog"
         aria-labelledby="declarationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="declarationModalLabel">Relatório de Declarações de Saúde do Viajante</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    @php
                        $reportController  = \App\Http\Controllers\Admin\DeclarationReportController::class;
                        $availableColumns  = $reportController::availableColumns();
                        $defaultColumns    = $reportController::defaultColumns();
                        $statusOptions     = $reportController::statusOptions();
                        $airlineOptions    = $reportController::airlineOptions();
                        $riskCountryOptions = $reportController::riskCountryOptions();
                    @endphp

                    <div class="form-group row">
                        <div class="col-md-6">
                            <label>Estado da Triagem <span class="text-danger">*</span></label>
                            <select required name="status" class="form-control">
                                <option value="">--Selecione um Estado--</option>
                                @foreach ($statusOptions as $statusValue => $statusLabel)
                                    <option value="{{ $statusValue }}">{{ mb_strtoupper($statusLabel) }}</option>
                                @endforeach
                                <option value="TODAS">TODOS</option>
                            </select>
                        </div>

                        {{-- Triagem não é o mesmo que estado: um viajante sem sintomas
                             vindo de país sob vigilância também entra em COM ALERTA --}}
                        <div class="col-md-6">
                            <label>Sinalização Sanitária <span class="text-danger">*</span></label>
                            <select required name="screening" class="form-control">
                                <option value="">--Selecione a Sinalização--</option>
                                <option value="COM_ALERTA">COM SINTOMAS OU EXPOSIÇÃO A PAÍS SOB VIGILÂNCIA</option>
                                <option value="SEM_ALERTA">SEM SINTOMAS E SEM EXPOSIÇÃO</option>
                                <option value="TODAS">TODAS</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-md-3">
                            <label>Chegada a partir de</label>
                            <input type="date" name="arrival_from" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label>Chegada até</label>
                            <input type="date" name="arrival_to" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label>Companhia Aérea</label>
                            <select name="airline_name" class="form-control">
                                <option value="">TODAS</option>
                                @foreach ($airlineOptions as $airlineName)
                                    <option value="{{ $airlineName }}">{{ $airlineName }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label>País Sob Vigilância Visitado</label>
                            <select name="risk_country" class="form-control">
                                <option value="">INDIFERENTE</option>
                                @foreach ($riskCountryOptions as $countryValue => $countryLabel)
                                    <option value="{{ $countryValue }}">{{ mb_strtoupper($countryLabel) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <hr>

                    <div class="form-group row">
                        <div class="col-md-12 d-flex justify-content-between align-items-center mb-2">
                            <b>Incluir Colunas no Relatório</b>
                            <div>
                                {{-- As sete perguntas clínicas de uma só vez: é a selecção
                                     mais pedida pela equipa de vigilância --}}
                                <button type="button" id="toggleClinicalColumns" class="btn btn-sm btn-outline-danger">
                                    Marcar questionário clínico
                                </button>
                                <button type="button" id="toggleAllColumns" class="btn btn-sm btn-outline-secondary">
                                    Marcar / Desmarcar todos
                                </button>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="row text-left mx-2">
                                @php
                                    $clinicalColumns = array_merge(
                                        array_keys(\App\Models\TravelerDeclaration::CLINICAL_QUESTIONS),
                                        ['other_symptoms_detail']
                                    );
                                @endphp

                                @foreach ($availableColumns as $key => $label)
                                    <div class="form-check col-md-4 mb-1">
                                        <input type="checkbox"
                                               class="form-check-input column-checkbox {{ in_array($key, $clinicalColumns, true) ? 'clinical-checkbox' : '' }}"
                                               id="column_{{ $key }}"
                                               name="columns[]"
                                               value="{{ $key }}"
                                               {{ in_array($key, $defaultColumns) ? 'checked' : '' }}>
                                        <small><label class="mb-0" for="column_{{ $key }}">{{ $label }}</label></small>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit"
                                formaction="{{ route('admin.declaration.report.pdf') }}"
                                class="btn btn-primary">
                            <i class="fa fa-file-pdf-o"></i> Imprimir PDF
                        </button>

                        <button type="submit"
                                formaction="{{ route('admin.declaration.report.excel') }}"
                                formtarget="_self"
                                class="btn btn-success">
                            <i class="fa fa-file-excel-o"></i> Exportar Excel
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggleButton = document.getElementById('toggleAllColumns');
        const clinicalButton = document.getElementById('toggleClinicalColumns');

        if (toggleButton) {
            toggleButton.addEventListener('click', function () {
                const checkboxes = document.querySelectorAll('.column-checkbox');
                const shouldCheck = Array.from(checkboxes).some(box => !box.checked);
                checkboxes.forEach(box => box.checked = shouldCheck);
            });
        }

        if (clinicalButton) {
            clinicalButton.addEventListener('click', function () {
                const checkboxes = document.querySelectorAll('.clinical-checkbox');
                const shouldCheck = Array.from(checkboxes).some(box => !box.checked);
                checkboxes.forEach(box => box.checked = shouldCheck);
            });
        }
    });
</script>
@endpush
