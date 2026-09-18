<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller
{
    // Laravel 11+ ya no incluye $this->authorize() en el controlador base.
    use AuthorizesRequests;
}
