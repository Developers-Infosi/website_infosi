{{-- Screening status badge. Stored in English, shown in Portuguese.
     Single source of truth: every view includes this partial.

     inline-block é obrigatório: num elemento inline o padding vertical não
     empurra a linha anterior e o badge sobrepõe-se ao texto de cima. --}}
@php
    $statusMap = [
        'RECEIVED' => ['label' => 'Recebida', 'class' => 'bg-primary'],
        'ALERT'    => ['label' => 'Alerta', 'class' => 'bg-danger'],
        'VERIFIED' => ['label' => 'Verificada', 'class' => 'bg-success'],
    ];
    $current = $statusMap[$status] ?? ['label' => $status, 'class' => 'bg-secondary'];
@endphp

<span class="{{ $current['class'] }} d-inline-block px-2 py-1 rounded text-white"
    style="font-size:.72rem;font-weight:700;line-height:1.2;white-space:nowrap;">
    {{ $current['label'] }}
</span>
