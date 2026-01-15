<?php

if (!function_exists('remember_cookie_name')) {
    function remember_cookie_name(): string
    {
        return 'decl_remember';
    }
}

if (!function_exists('remember_pack')) {
    // selector:validator formátum
    function remember_pack(string $selector, string $validator): string
    {
        return $selector . ':' . $validator;
    }
}

if (!function_exists('remember_unpack')) {
    function remember_unpack(string $value): ?array
    {
        $parts = explode(':', $value, 2);
        if (count($parts) !== 2)
            return null;
        if ($parts[0] === '' || $parts[1] === '')
            return null;
        return [$parts[0], $parts[1]];
    }
}

if (!function_exists('remember_device_cookie_name')) {
    function remember_device_cookie_name(): string
    {
        return 'decl_device';
    }
}

if (!function_exists('remember_device_id')) {
    function remember_device_id(): string
    {
        $name = remember_device_cookie_name();
        $val = $_COOKIE[$name] ?? null;

        if (is_string($val) && preg_match('/^[a-f0-9]{32}$/', $val)) {
            return $val;
        }

        $val = bin2hex(random_bytes(16)); // 32 hex
        setcookie($name, $val, [
            'expires' => time() + 365 * 24 * 60 * 60, // 1 év
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        return $val;
    }
}

