<?php

namespace App\Repositories\Interfaces;

use App\Models\Usuario;

interface UsuarioRepositoryInterface
{
    public function create(Usuario $usuario): int;
    public function findByEmail(string $email): ?Usuario;
    public function findById(int $id): ?Usuario;
}