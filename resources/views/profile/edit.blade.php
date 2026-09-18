@extends('layouts.app')

@section('title', 'Mi cuenta - PromptVault')

@section('content')
<div class="prompts-container">
    <h2>Mi cuenta</h2>

    @if(session('status') === 'profile-updated')
        <div class="alert alert-success">Perfil actualizado.</div>
    @endif

    <form method="POST" action="{{ route('profile.update') }}">
        @csrf
        @method('PATCH')
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
        <p>
            <label>Nombre</label><br>
            <input type="text" name="name" required value="{{ old('name', $user->name) }}">
        </p>
        <p>
            <label>Correo</label><br>
            <input type="email" name="email" required value="{{ old('email', $user->email) }}">
        </p>
        <button type="submit" class="btn btn-primary">Guardar</button>
    </form>

    <h3>Eliminar cuenta</h3>
    <form method="POST" action="{{ route('profile.destroy') }}">
        @csrf
        @method('DELETE')
        @if ($errors->userDeletion->any())
            <div class="alert alert-danger">{{ $errors->userDeletion->first('password') }}</div>
        @endif
        <p>
            <label>Contrasena actual</label><br>
            <input type="password" name="password" required>
        </p>
        <button type="submit" class="btn btn-danger">Eliminar mi cuenta</button>
    </form>
</div>
@endsection
