{{-- Clinical answer badge. YES is the only one that needs attention. --}}
@php
    $answerMap = [
        'YES'    => ['label' => 'Sim', 'class' => 'badge-danger'],
        'NO'     => ['label' => 'Não', 'class' => 'badge-secondary'],
        'UNSURE' => ['label' => 'Não sabe', 'class' => 'badge-warning'],
    ];
    $current = $answerMap[$answer] ?? ['label' => $answer, 'class' => 'badge-light'];
@endphp

<span class="badge {{ $current['class'] }} p-2">{{ $current['label'] }}</span>
