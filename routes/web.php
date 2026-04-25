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
    Router::get('', 'DashboardController@index');
    Router::get('/', 'DashboardController@index');
    Router::get('/perfil', 'DashboardController@perfil');
    
    Router::get('/avisos', 'AvisosController@index');
    Router::post('/avisos', 'AvisosController@store');
    Router::get('/avisos/{id}/edit', 'AvisosController@edit');
    Router::post('/avisos/{id}', 'AvisosController@update');
    Router::post('/avisos/{id}/delete', 'AvisosController@destroy');
   
    // Professor
    Router::get('/professores', 'ProfessoresController@index');
    Router::get('/professores/create', 'ProfessoresController@create');
    Router::post('/professores', 'ProfessoresController@store');
    Router::get('/professores/{id}/edit', 'ProfessoresController@edit');
    Router::post('/professores/{id}', 'ProfessoresController@update');
    Router::post('/professores/{id}/delete', 'ProfessoresController@destroy');

    // Classe
    Router::get('/classes', 'ClasseController@index');
    Router::get('/classes/create', 'ClasseController@create');
    Router::get('/classe/show', 'ClasseController@show');
    Router::post('/classes', 'ClasseController@store');
    Router::get('/classes/{id}/edit', 'ClasseController@edit');
    Router::post('/classes/{id}', 'ClasseController@update');
    Router::post('/classes/{id}/delete', 'ClasseController@destroy');

    // Turmas
    Router::get('/turmas', 'TurmaController@index');
    Router::get('/turma/create', 'TurmaController@create');
    Router::get('/turma/show', 'TurmaController@show');
    Router::post('/turma', 'TurmaController@store');
    Router::get('/turma/{id}/edit', 'TurmaController@edit');
    Router::post('/turma/{id}', 'TurmaController@update');
    Router::post('/turma/{id}/delete', 'TurmaController@destroy');

    // Alunos
    Router::get('/alunos', 'AlunosController@index');
    Router::get('/alunos/create', 'AlunosController@create');
    Router::post('/alunos', 'AlunosController@store');
    Router::get('/alunos/{id}/edit', 'AlunosController@edit');
    Router::post('/alunos/{id}', 'AlunosController@update');
    Router::post('/alunos/{id}/delete', 'AlunosController@destroy');
    Router::get('/aluno/show', 'AlunosController@show');
    
    // Encarregado
    Router::get('/encarregados', 'EncarregadoController@index');
    Router::get('/encarregado/show', 'EncarregadoController@show');
    

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

// Rota fallback para páginas não encontradas
Router::get('/{any}', function () {
    http_response_code(404);
    View::render('errors/404', [
        'message' => 'A página que você tentou acessar não foi encontrada.'
    ]);
})->where(['any' => '.*']);

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
