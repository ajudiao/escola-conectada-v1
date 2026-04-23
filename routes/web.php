<?php

use App\Core\View;
use Pecee\SimpleRouter\SimpleRouter as Router;
use App\Middleware\AdminMiddleware;
use App\Middleware\AuthMiddleware;
use App\Middleware\ProfessorMiddleware;
use App\Middleware\EncarregadoMiddleware;
use App\Controllers\AuthController;
use Pecee\Http\Request;
use Pecee\SimpleRouter\Route\Route;



// Login routes
Router::get('/login', [AuthController::class, 'loginForm']);
Router::post('/login', [AuthController::class, 'login']);

Router::group([
    'middleware' => AuthMiddleware::class
], function () {
    Router::get('/', 'App\Controllers\Admin\DashboardController@index');
});

//
// ------------------------
// PAINEL ADMIN (PROTEGIDO)
// ------------------------
//
Router::group([
    'prefix' => '/admin',
    'namespace' => 'App\Controllers\Admin',
    'middleware' => AdminMiddleware::class
], function () {

    // Dashboard
    Router::get('/', 'DashboardController@index');
    Router::get('/notificacoes', 'NotificationsController@index');

    //
    // USUÁRIOS (REST)
    //
    Router::get('/usuarios', 'UsuariosController@index');
    Router::post('/usuarios', 'UsuariosController@store');
    Router::post('/usuarios/update/{id}', 'UsuariosController@update');
    Router::get('/usuarios/delete/{id}', 'UsuariosController@delete');

    // Perfil e Configurações
    Router::get('/perfil', 'PerfilController@index');
    Router::post('/perfil/foto', 'PerfilController@updatePhoto');
    Router::post('/perfil/senha', 'PerfilController@changePassword');

    Router::get('/configuracoes', 'DashboardController@configuracoes');
    Router::post('/configuracoes/backup', 'DashboardController@backup');
    Router::post('/configuracoes/delete-all', 'DashboardController@deleteAllData');
    Router::get('/configuracoes/export', 'DashboardController@exportCsv');

    Router::get('/logout', 'AuthController@logout');
});

//
// ------------------------
// PAINEL PROFESSOR (PROTEGIDO)
// ------------------------
//
Router::group([
    'prefix' => '/professor',
    'namespace' => 'App\Controllers\Professor',
    'middleware' => ProfessorMiddleware::class
], function () {

    Router::get('/', 'DashboardController@index');
    Router::get('/turmas', 'TurmasController@index');
    Router::get('/turmas/{id}', 'TurmasController@show');
    Router::get('/faltas', 'FaltasController@index');
    Router::get('/mini-pauta', 'PautaController@index');
    Router::get('/perfil', 'PerfilController@index');
    Router::post('/perfil/foto', 'PerfilController@updatePhoto');
    Router::post('/perfil/senha', 'PerfilController@changePassword');
    Router::get('/avisos', 'AvisosController@index');
    Router::get('/logout', 'AuthController@logout');
});

//
// ------------------------
// PAINEL ENCARREGADO (PROTEGIDO)
// ------------------------
//
Router::group([
    'prefix' => '/encarregado',
    'namespace' => 'App\Controllers\Encarregado',
    'middleware' => EncarregadoMiddleware::class
], function () {

    Router::get('/', 'DashboardController@index');
    Router::get('/educandos', 'EducandosController@index');
    Router::get('/notas', 'NotasController@index');
    Router::get('/faltas', 'FaltasController@index');
    Router::get('/horario', 'HorarioController@index');
    Router::get('/mensagens', 'MensagensController@index');
    Router::get('/perfil', 'PerfilController@index');
    Router::post('/perfil/foto', 'PerfilController@updatePhoto');
    Router::post('/perfil/senha', 'PerfilController@changePassword');
    Router::get('/avisos', 'AvisosController@index');
    Router::get('/logout', 'AuthController@logout');
});

//
// ------------------------
// ERROS
// ------------------------
//
Router::error(function (Request $request, \Throwable $exception) {

    // Sempre loga o erro (boa prática)
    error_log($exception->getMessage());

    // Verifica se é um erro 404 (rota não encontrada)
    $is404 = strpos($exception->getMessage(), 'Route not found') !== false || 
             $exception->getCode() === 404;

    if ($is404) {
        http_response_code(404);
        View::render('errors/404', [
            'message' => 'Página não encontrada.'
        ]);
        return;
    }

    // MODO DESENVOLVIMENTO (para outros erros)
    if (DEBUG) {

        http_response_code(500);

        echo "<h1>Erro:</h1>";
        echo "<p><strong>Mensagem:</strong> " . $exception->getMessage() . "</p>";
        echo "<p><strong>Arquivo:</strong> " . $exception->getFile() . "</p>";
        echo "<p><strong>Linha:</strong> " . $exception->getLine() . "</p>";

        echo "<pre>";
        print_r($exception->getTrace());
        echo "</pre>";

        return;
    }

    // PRODUÇÃO (usuário nunca vê erro técnico)
    http_response_code(500);

    View::render('errors/500', [
        'message' => 'Erro interno. Tente novamente mais tarde.'
    ]);
});