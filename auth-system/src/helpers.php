<?php

/**
 * Funções Helper para URLs e Rotas
 * Resolve problemas de caminhos em subdiretórios
 */

if (!function_exists('getBasePath')) {
    /**
     * Obtém o caminho base da aplicação
     */
    function getBasePath(): string
    {
        // Detecta automaticamente o base path
        $scriptName = $_SERVER['SCRIPT_NAME']; // /email-management-system/auth-system/public/index.php
        $basePath = str_replace('/index.php', '', $scriptName);
        return $basePath;
    }
}

if (!function_exists('url')) {
    /**
     * Gera URL completa com base path
     *
     * @param string $path Caminho relativo (ex: '/login', '/users')
     * @return string URL completa
     */
    function url(string $path = ''): string
    {
        $basePath = getBasePath();

        // Remove barra inicial se existir
        $path = ltrim($path, '/');

        // Monta URL
        if (empty($path)) {
            return $basePath ?: '/';
        }

        return $basePath . '/' . $path;
    }
}

if (!function_exists('redirect')) {
    /**
     * Redireciona para uma URL
     *
     * @param string $path Caminho relativo (ex: '/login', '/dashboard')
     */
    function redirect(string $path): void
    {
        header('Location: ' . url($path));
        exit;
    }
}

if (!function_exists('currentUrl')) {
    /**
     * Obtém a URL atual completa
     */
    function currentUrl(): string
    {
        return $_SERVER['REQUEST_URI'] ?? '/';
    }
}

if (!function_exists('currentPath')) {
    /**
     * Obtém apenas o path atual (sem query string)
     */
    function currentPath(): string
    {
        return parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    }
}

if (!function_exists('asset')) {
    /**
     * Gera URL para assets (CSS, JS, imagens)
     *
     * @param string $path Caminho do asset (ex: 'css/style.css')
     * @return string URL completa
     */
    function asset(string $path): string
    {
        return url($path);
    }
}
