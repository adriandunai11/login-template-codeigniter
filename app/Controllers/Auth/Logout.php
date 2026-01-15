<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\DeclRememberTokenModel;

class Logout extends BaseController
{
    public function index()
    {
        helper(['remember']);

        $cookieName = remember_cookie_name();
        $cookieVal = $_COOKIE[$cookieName] ?? null;

        if (is_string($cookieVal) && $cookieVal !== '') {
            $parts = remember_unpack($cookieVal);
            if ($parts) {
                [$selector, $validator] = $parts;
                (new DeclRememberTokenModel())->deleteBySelector($selector);
            }
        }

        $this->deleteCookie($cookieName);

         $this->deleteCookie(remember_device_cookie_name());

        session()->destroy();

        return redirect()->to(site_url('auth/login'));
    }

    private function deleteCookie(string $name): void
    {
        $secure = $this->request->isSecure();

        setcookie($name, '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        unset($_COOKIE[$name]);
    }
}
