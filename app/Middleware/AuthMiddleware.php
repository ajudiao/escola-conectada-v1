<?php

namespace App\Middleware;

use Pecee\Http\Middleware\IMiddleware;
use Pecee\Http\Request;

class AuthMiddleware implements IMiddleware
{
    public function handle(Request $request): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['logged_in']) || !isset($_SESSION['user_perfil']       ) || $_SESSION['user_perfil'] !== 'admin') {
            header('Location: /login');
            exit;
        }
    }
}