<?php

namespace App\Helpers;


class Helpers
{
    public static function detectEnvironment(array $hostsByEnv = []): string
    {
        $host = strtolower(trim($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? ''));
        $host = preg_replace('/:\d+$/', '', $host);

        if ($host === '') {
            return defined('APP_ENV') ? APP_ENV : 'production';
        }

        if (empty($hostsByEnv)) {
            $hostsByEnv = [
                'development' => ['localhost', '127.0.0.1', '::1'],
                'production' => ['seu-dominio.com', '*.meudominio.com'],
            ];
        }

        foreach ($hostsByEnv as $env => $hosts) {
            foreach ((array) $hosts as $pattern) {
                if ($pattern === $host || fnmatch($pattern, $host)) {
                    return $env;
                }
            }
        }

        return defined('APP_ENV') ? APP_ENV : 'production';
    }

    public static function isDevelopment(): bool
    {
        return self::detectEnvironment() === 'development';
    }

    public static function isProduction(): bool
    {
        return self::detectEnvironment() === 'production';
    }

    public static function getBaseUrl(): string
    {
        if (!defined('URL_DESENVOLVIMENTO') || !defined('URL_PRODUCAO')) {
            return '/';
        }

        return self::isProduction() ? URL_PRODUCAO : URL_DESENVOLVIMENTO;
    }

    public static function asset(string $path): string
    {
        return rtrim(self::getBaseUrl(), '/') . '/' . ltrim($path, '/');
    }

    public static function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }

    public static function currentUrl(): string
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? '');
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        return $scheme . '://' . $host . $uri;
    }

    public static function sanitize(string $value): string
    {
        return htmlspecialchars(trim($value), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
