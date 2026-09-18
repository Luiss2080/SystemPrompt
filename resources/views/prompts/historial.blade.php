@extends('layouts.app')

@section('title', 'Historial - PromptVault')

@section('content')
<div class="prompts-container">
    <h2>Historial de {{ $prompt->titulo }}</h2>
    <ul>
        @forelse($actividades as $actividad)
            <li>
                {{ $actividad->fecha?->format('d/m/Y H:i') }} - {{ $actividad->accion }}
                @if($actividad->descripcion) : {{ $actividad->descripcion }} @endif
                ({{ $actividad->user?->name }})
            </li>
        @empty
            <li>Sin actividad registrada.</li>
        @endforelse
    </ul>
    {{ $actividades->links() }}
    <a href="{{ route('prompts.show', $prompt) }}">Volver</a>
</div>
@endsection
