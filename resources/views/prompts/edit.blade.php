@extends('layouts.app')

@section('title', 'Editar Prompt - PromptVault')

@section('content')
<div class="prompts-container">
    <h2>Editar prompt</h2>
    <form method="POST" action="{{ route('prompts.update', $prompt) }}">
        @method('PUT')
        @include('prompts._form')
        <p>
            <label>Motivo del cambio</label><br>
            <input type="text" name="motivo_cambio" value="{{ old('motivo_cambio') }}">
        </p>
        <button type="submit" class="btn btn-primary">Actualizar</button>
        <a href="{{ route('prompts.show', $prompt) }}">Cancelar</a>
    </form>
</div>
@endsection
