<?php

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\View\RendererInterface;

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        return site_url($path);
    }
}

if (!function_exists('assets_url')) {
    function assets_url(string $path = ''): string
    {
        $path = ltrim($path, '/');
        return base_url('assets' . ($path !== '' ? '/' . $path : ''));
    }
}

if (!function_exists('request')) {
    /**
     * @return IncomingRequest
     */
    function request(): IncomingRequest
    {
        /** @var IncomingRequest */
        return service('request');
    }
}

if (!function_exists('post')) {
    function post(string $key, $default = null)
    {
        $val = request()->getPost($key);
        return $val !== null ? $val : $default;
    }
}

if (!function_exists('get')) {
    function get(string $key, $default = null)
    {
        $val = request()->getGet($key);
        return $val !== null ? $val : $default;
    }
}

if (!function_exists('input')) {
    function input(string $key, $default = null)
    {
        $val = request()->getVar($key);
        return $val !== null ? $val : $default;
    }
}

if (!function_exists('view_set_data')) {
    function view_set_data(array $data): void
    {
        /** @var RendererInterface $view */
        $view = \Config\Services::renderer();

        $view->setData($data);
    }
}

if (!function_exists('setPageData')) {
    function setPageData(array $data): void
    {
        view_set_data(['_page' => (object) $data]);
    }
}

if (!function_exists('setDefaultViewData')) {
    function setDefaultViewData(): void
    {
        setPageData([
            'title' => '',
            'menu' => '',
            'submenu' => '',
        ]);
    }
}

if (!function_exists('postAllowed')) {
    function postAllowed(): bool
    {
        if (request()->getMethod(true) !== 'POST') {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Invalid Request');
        }

        return true;
    }
}

if (!function_exists('obfuscate_email')) {
    function obfuscate_email(string $email): string
    {
        if ($email === '' || strpos($email, '@') === false) {
            return $email;
        }

        [$local, $domain] = explode('@', $email, 2);

        if ($local === '') {
            return $email;
        }

        $len = (int) floor(strlen($local) / 2);
        if ($len < 1) {
            return '*' . '@' . $domain;
        }

        return substr($local, 0, $len) . str_repeat('*', $len) . '@' . $domain;
    }
}

if (!function_exists('ip_address')) {
    function ip_address(): string
    {
        return request()->getIPAddress() ?? 'UNKNOWN';
    }
}
