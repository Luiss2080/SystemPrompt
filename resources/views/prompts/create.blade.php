@extends('layouts.app')

@section('title', 'Nuevo Prompt - PromptVault')

@section('content')
<div class="prompts-container">
    <h2>Nuevo prompt</h2>
    <form method="POST" action="{{ route('prompts.store') }}">
        @include('prompts._form')
        <button type="submit" class="btn btn-primary">Guardar</button>
        <a href="{{ route('prompts.index') }}">Cancelar</a>
    </form>
</div>
@endsection
