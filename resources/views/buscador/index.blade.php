@extends('layouts.app')

@section('title', 'Buscador Global')

@section('css')
<link rel="stylesheet" href="{{ asset('css/buscador/index.css') }}">
@endsection

@section('content')
<div class="buscador-container">
    <div class="search-header">
        <h2>Buscador global</h2>
        <p class="search-subtitle">Prompts, categorias y etiquetas</p>
    </div>

    <form method="GET" action="{{ route('buscador.index') }}">
        <input type="text" name="query" value="{{ $termino }}" placeholder="Escribe al menos 2 caracteres..." autofocus>
        <button type="submit" class="btn">Buscar</button>
    </form>

    @if($termino !== '')
        <h3>Resultados para "{{ $termino }}"</h3>
        <ul>
            @forelse($resultados as $resultado)
                <li>
                    <a href="{{ $resultado['url'] }}">{{ $resultado['titulo'] }}</a>
                    <small>({{ $resultado['tipo'] }})</small> - {{ $resultado['descripcion'] }}
                </li>
            @empty
                <li>Sin resultados.</li>
            @endforelse
        </ul>
    @endif
</div>
@endsection
