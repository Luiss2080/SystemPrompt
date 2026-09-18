@extends('layouts.app')

@section('title', $prompt->titulo . ' - PromptVault')

@section('content')
<div class="prompts-container">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <h2>{{ $prompt->titulo }} <small>v{{ $prompt->version_actual }}</small></h2>
    @if($prompt->descripcion)<p>{{ $prompt->descripcion }}</p>@endif
    <pre style="white-space:pre-wrap;">{{ $prompt->contenido }}</pre>

    <p>
        {{ $prompt->categoria?->nombre ?? 'Sin categoria' }}
        @if($prompt->ia_destino) &middot; {{ $prompt->ia_destino }} @endif
        @foreach($prompt->etiquetas as $etiqueta) <span>#{{ $etiqueta->nombre }}</span> @endforeach
    </p>

    @can('update', $prompt)
        <a href="{{ route('prompts.edit', $prompt) }}" class="btn">Editar</a>
        <a href="{{ route('prompts.historial', $prompt) }}" class="btn">Historial</a>
        <form method="POST" action="{{ route('prompts.destroy', $prompt) }}" style="display:inline;">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger">Eliminar</button>
        </form>
    @endcan

    <h3>Versiones</h3>
    <ul>
        @foreach($prompt->versiones->sortByDesc('numero') as $version)
            <li>
                v{{ $version->numero }} - {{ $version->fecha_version?->format('d/m/Y H:i') }}
                @if($version->motivo_cambio) ({{ $version->motivo_cambio }}) @endif
                @can('update', $prompt)
                    <form method="POST" action="{{ route('prompts.restaurar', [$prompt, $version]) }}" style="display:inline;">
                        @csrf
                        <button type="submit">Restaurar</button>
                    </form>
                @endcan
            </li>
        @endforeach
    </ul>
</div>
@endsection
