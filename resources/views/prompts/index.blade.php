@extends('layouts.app')

@section('title', 'Mis Prompts - PromptVault')

@section('content')
<div class="prompts-container">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
        <h2>Mis Prompts</h2>
        <a href="{{ route('prompts.create') }}" class="btn btn-primary">Nuevo prompt</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="GET" action="{{ route('prompts.index') }}" style="display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1rem;">
        <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar...">
        <select name="categoria_id">
            <option value="">Todas las categorias</option>
            @foreach($categorias as $categoria)
                <option value="{{ $categoria->id }}" @selected(request('categoria_id') == $categoria->id)>{{ $categoria->nombre }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn">Filtrar</button>
    </form>

    @forelse($prompts as $prompt)
        <div class="prompt-card" style="border:1px solid #ddd;border-radius:8px;padding:1rem;margin-bottom:.75rem;">
            <h3><a href="{{ route('prompts.show', $prompt) }}">{{ $prompt->titulo }}</a></h3>
            <p>{{ \Illuminate\Support\Str::limit($prompt->descripcion ?? $prompt->contenido, 140) }}</p>
            <small>
                {{ $prompt->categoria?->nombre ?? 'Sin categoria' }}
                @if($prompt->ia_destino) &middot; {{ $prompt->ia_destino }} @endif
                &middot; v{{ $prompt->version_actual }}
                @foreach($prompt->etiquetas as $etiqueta) <span>#{{ $etiqueta->nombre }}</span> @endforeach
            </small>
        </div>
    @empty
        <p>Todavia no tienes prompts. <a href="{{ route('prompts.create') }}">Crea el primero</a>.</p>
    @endforelse

    {{ $prompts->withQueryString()->links() }}
</div>
@endsection
