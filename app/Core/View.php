<?php

namespace App\Core;

use App\Helpers\Helpers;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Twig\TwigFunction;

class View
{
    private static $twig;

    private static function init()
    {
        if (self::$twig === null) {

            $loader = new FilesystemLoader(BASE_PATH . '/escola/public/views');

            self::$twig = new Environment($loader, [
                'cache' => false,
                'debug' => true
            ]);

            /*
            |--------------------------------------------------------------------------
            | Variáveis globais
            |--------------------------------------------------------------------------
            */
            self::$twig->addGlobal('app_name', APP_NAME);
            self::$twig->addGlobal('base_url', URL_BASE);
            self::$twig->addGlobal('companyLogo', URL_ASSETS_SITE . '/logotipo.png');
            self::$twig->addGlobal('localImage', URL_ASSETS_SITE . '/local.png');
            // self::$twig->addGlobal('flashMessage', Helpers::getFlash());
            self::$twig->addGlobal('currentPath', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

            /*
            |--------------------------------------------------------------------------
            | Funções
            |--------------------------------------------------------------------------
            */
            // Caminhos para assets estáticos
            self::$twig->addFunction(
                new TwigFunction('asset', function ($path) {
                    return '/assets/images/' . ltrim($path, '/');
                })
            );

            // URL base do sistema
            self::$twig->addFunction(
                new TwigFunction('url', function ($path = '') {
                    return '/' . ltrim($path, '/');
                })
            );

            // Caminho completo de imagens de usuários
            self::$twig->addFunction(
                new TwigFunction('userImage', function (?string $filename) {
                    $uploadPath = '/uploads/users/'; // URL pública para navegador
                    $filePath = BASE_PATH . '/public/uploads/users/' . $filename; // caminho real no servidor

                    if ($filename && file_exists($filePath)) {
                        return $uploadPath . ltrim($filename, '/');
                    }

                    // Fallback caso não exista imagem
                    return 'https://i.pravatar.cc/50?img=12';
                })
            );

            // Caminho completo de imagens de carros
            self::$twig->addFunction(
                new TwigFunction('carImage', function (?string $filename) {
                    $uploadPath = '/uploads/cars/'; // URL pública para navegador
                    $filePath = BASE_PATH . '/public/uploads/cars/' . $filename; // caminho real no servidor

                    if ($filename && file_exists($filePath)) {
                        return $uploadPath . ltrim($filename, '/');
                    }

                    // Fallback caso não exista imagem
                    return 'https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?w=400&h=300&fit=crop';
                })
            );

            // Notificações e usuário global para o header
            // $notifications = (new \App\Repositories\NotificationRepository())->getNotifications(5);
            // self::$twig->addGlobal('notifications', $notifications);
            // self::$twig->addGlobal('notificationsCount', count($notifications));
            // self::$twig->addGlobal('userAvatar', isset($_SESSION['user_foto']) ? '/uploads/users/' . $_SESSION['user_foto'] : 'https://i.pravatar.cc/50?img=12');
            // self::$twig->addGlobal('userName', $_SESSION['user_nome'] ?? 'Administrador');
            // self::$twig->addGlobal('userPerfil', $_SESSION['user_perfil'] ?? '');
            // self::$twig->addGlobal('isAdmin', isset($_SESSION['user_perfil']) && $_SESSION['user_perfil'] === 'Administrador');
            // self::$twig->addGlobal('currentPath', $_SERVER['REQUEST_URI'] ?? '/');

            // Configurações do site
            // $settings = (new \App\Repositories\SiteSettingRepository())->getAll();
            // self::$twig->addGlobal('settings', $settings);
        }

        return self::$twig;
    }
    
    public static function render(string $template, array $data = [])
    {
        $twig = self::init();

        echo $twig->render($template . '.twig', $data);
    }
}
