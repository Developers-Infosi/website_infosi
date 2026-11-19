{{-- Clinical answers. The questions come from the model, so adding or removing
     one never requires touching this view. Expects: $declaration. --}}
<table class="table table-sm table-bordered mb-0">
    <tbody>
        @foreach (\App\Models\TravelerDeclaration::CLINICAL_QUESTIONS as $field => $label)
            <tr>
                <td class="text-dark">{{ $label }}</td>
                <td class="text-right" style="width: 140px">
                    @include('admin.declaration.partials._answer', ['answer' => $declaration->$field])
                </td>
            </tr>
        @endforeach

        @if ($declaration->other_symptoms === 'YES')
            <tr>
                <td class="text-dark">Outros sintomas descritos pelo viajante</td>
                <td class="text-right text-dark"><b>{{ $declaration->other_symptoms_detail }}</b></td>
            </tr>
        @endif
    </tbody>
</table>
