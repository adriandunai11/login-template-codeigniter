<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RedirectResponse;

class Login extends BaseController
{
    public function index()
    {
        if (logged_in()) {
            return redirect()->to('/');
        }

        return view('auth/login', [
            'title' => 'Bejelentkezés',
            'error' => session()->getFlashdata('error'),
        ]);
    }

    public function authenticate(): RedirectResponse
    {
        log_message('debug', 'AUTH authenticate reached');

        $svc = service('declAuthService');

        $result = $svc->attemptLogin(
            (string) $this->request->getPost('antra_id'),
            (string) $this->request->getPost('password'),
        );

        if (!($result['ok'] ?? false)) {
            return redirect()->to('/auth/login')
                ->with('error', $result['error'] ?? 'Sikertelen belépés.');
        }

        $user = $result['user'];
        $auth = $result['auth'];

        session()->regenerate(true);
        session()->set([
            'decl_logged_in' => true,
            'decl_user_id' => (int) $user->id,
            'decl_antra_id' => (string) $user->antra_id,
            'decl_full_name' => (string) $user->full_name,
            'decl_must_change_password' => $auth->mustChangePassword(),
        ]);

        $remember = $this->request->getPost('remember_me') === '1';

        if ($remember) {
            $ua = (string) $this->request->getUserAgent();
            $ip = (string) $this->request->getIPAddress();

            $deviceId = remember_device_id();
            $token = $svc->createRememberMeToken((int) $user->id, $deviceId, $ua, $ip);
            $value = remember_pack($token['selector'], $token['validator']);

            // cookie 30 nap
            setcookie(
                remember_cookie_name(),
                $value,
                [
                    'expires' => $token['expires'],
                    'path' => '/',
                    'secure' => true,      // https
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]
            );
        }

        if ($auth->mustChangePassword()) {
            return redirect()->to('/auth/password/change');
        }

        return redirect()->to('/');
    }


}
