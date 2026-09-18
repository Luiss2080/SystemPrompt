@php
    // Layout comun para pantallas de contenido: reutiliza el shell (sidebar,
    // header, footer) del componente de dashboard segun el rol del usuario.
    $user = Auth::user();
    $userRole = $user && $user->role ? $user->role->nombre : session('user_role', 'guest');

    $shell = match ($userRole) {
        'admin' => 'components.administrador',
        'user' => 'components.usuario',
        'collaborator' => 'components.colaborador',
        default => 'components.invitado',
    };
@endphp
@include($shell, ['stats' => $stats ?? []])
