<?php

namespace App\Middleware;

use Pecee\Http\Middleware\IMiddleware;
use Pecee\Http\Request;

class ProfessorMiddleware implements IMiddleware
{
    public function handle(Request $request): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['logged_in']) || !isset($_SESSION['user_perfil']) || !in_array($_SESSION['user_perfil'], ['professor', 'Professor'], true)) {
            header('Location: /login');
            exit;
        }
    }
}
