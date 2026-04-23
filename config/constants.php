<?php
// -----------------------------
// CONFIGURAÇÃO DE URL E PATHS
// -----------------------------

define('URL_BASE', '/escola/'); // URL base do aplicativo
define('URL_DESENVOLVIMENTO', 'http://localhost/escola/'); // URL para desenvolvimento
define('URL_ASSETS_SITE','/assets/site/images'); // URL dos assets do site
define('URL_PRODUCAO', 'https://seu-dominio.com'); // URL
define('PUBLIC_PATH', __DIR__ . '/../public');           // caminho absoluto da pasta public
define('APP_PATH', __DIR__ . '/../app');                 // caminho absoluto da pasta app
define('ROUTES_PATH', __DIR__ . '/../routes');          // pasta das rotas
define('VIEWS_PATH', __DIR__ . '/../public/views');         // pasta das views

define('UPLOADS_PATH', PUBLIC_PATH . '/uploads'); // caminho absoluto para uploads
define('UPLOADS_URL', URL_BASE . 'uploads');      // URL base para acessar uploads

define('DEBUG', true); // Ativar ou desativar modo de depuração

// -----------------------------
// INFORMAÇÕES DO APLICATIVO
// -----------------------------
define('APP_NAME', 'Saeld Auto');
define('COMPANY_ADDRESS', 'Av. 21 de Janeiro, Ingonbota, Luanda');
define('COMPANY_PHONE', '+244 923 000 000');
define('COMPANY_EMAIL', 'contato@saeldauto.com');
/*define('APP_ENV', Helpers::detectEnvironment([
    'development' => ['localhost', '127.0.0.1', '::1'],
    'production' => ['seu-dominio.com', '*.meudominio.com'],
]));
*/
// -----------------------------
// BANCO DE DADOS
// -----------------------------
define('DB_HOST', 'localhost');
define('DB_NAME', 'stand_cars');
define('DB_USER', 'root');
define('DB_PASS', ''); // senha do MySQL

// -----------------------------
// CONFIGURAÇÕES DE ROTEAMENTO
// -----------------------------
define('DEFAULT_CONTROLLER', 'HomeController');
define('DEFAULT_METHOD', 'index');

// -----------------------------
// CONFIGURAÇÕES DE SESSÃO
// -----------------------------
define('SESSION_NAME', 'standcars_session');
define('SESSION_LIFETIME', 3600); // 1 hora

// -----------------------------
// OUTRAS CONFIGURAÇÕES ÚTEIS
// -----------------------------
define('TIMEZONE', 'Africa/Luanda');   // fuso horário
# define('DEBUG', Helpers::isDevelopment());
define('APP_VERSION', '1.0.0');        // versão do aplicativo