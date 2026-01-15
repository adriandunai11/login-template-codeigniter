<?php

use App\Models\DeclUserModel;

if (!function_exists('logged_in')) {
    function logged_in(): bool
    {
        return session()->get('decl_logged_in') === true
            && session()->get('decl_user_id') !== null;
    }
}

if (!function_exists('logged_id')) {
    function logged_id(): ?int
    {
        if (!logged_in())
            return null;
        return (int) session()->get('decl_user_id');
    }
}

if (!function_exists('logged_user')) {
    function logged_user(): ?object
    {
        static $cached = null;
        static $loaded = false;

        if ($loaded) {
            return $cached;
        }
        $loaded = true;

        $id = logged_id();
        if (!$id)
            return null;

        $model = new DeclUserModel();
        $cached = $model->find($id);
        return $cached;
    }
}

if (!function_exists('logged')) {
    function logged(string $key, $default = null)
    {
        $user = logged_user();
        if (!$user)
            return $default;

        if (is_object($user) && isset($user->{$key})) {
            return $user->{$key};
        }

        if (is_array($user) && array_key_exists($key, $user)) {
            return $user[$key];
        }

        return $default;
    }
}

if (!function_exists('logged_session')) {
    function logged_session(string $key, $default = null)
    {
        return session()->get('decl_' . $key) ?? $default;
    }
}