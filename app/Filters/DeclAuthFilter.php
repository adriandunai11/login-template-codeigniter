<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

use App\Models\DeclUserModel;
use App\Models\DeclUserAuthModel;
use App\Models\DeclRememberTokenModel;

class DeclAuthFilter implements FilterInterface
{    private array $publicPaths = [
        'auth/login',
        'auth/login/authenticate',
        'auth/logout',

        'auth/password/change',
        'auth/password/update',
    ];

    public function before(RequestInterface $request, $arguments = null)
    {
        helper(['auth', 'remember']);

        $path = $this->normalizePath($request);

        if ($this->isPublicPath($path)) {
            return;
        }

        if (!logged_in()) {
            $this->tryAutoLoginFromRememberCookie($request);
        }

        if (!logged_in()) {
            return redirect()->to(url('auth/login'));
        }

        if (
            logged_session('must_change_password') === true &&
            !$this->isPasswordChangePath($path)
        ) {
            return redirect()->to(site_url('auth/password/change'));
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // nincs teendő
    }

    private function normalizePath(RequestInterface $request): string
    {
        $path = strtolower(trim((string) $request->getUri()->getPath(), '/'));

        if (str_starts_with($path, 'index.php/')) {
            $path = substr($path, strlen('index.php/'));
        } elseif ($path === 'index.php') {
            $path = '';
        }

        return $path;
    }

    private function isPublicPath(string $path): bool
    {
        if (        
            str_starts_with($path, 'assets/') ||
            $path === 'favicon.ico' ||
            $path === 'robots.txt'
        ) {
            return true;
        }

        return in_array($path, $this->publicPaths, true);
    }

    private function isPasswordChangePath(string $path): bool
    {
        return in_array($path, [
            'auth/password/change',
            'auth/password/update',
        ], true);
    }

    private function tryAutoLoginFromRememberCookie(RequestInterface $request): void
    {
        $cookieName = remember_cookie_name();
        $cookieVal = $_COOKIE[$cookieName] ?? null;

        if (!is_string($cookieVal) || $cookieVal === '') {
            return;
        }

        $parts = remember_unpack($cookieVal);
        if (!$parts) {
            $this->deleteRememberCookie($request);
            return;
        }

        [$selector, $validator] = $parts;

        /** @var \App\Services\DeclAuthService $svc */
        $svc = service('declAuthService');

        $userId = $svc->consumeRememberMe($selector, $validator);
        if (!$userId) {
            (new DeclRememberTokenModel())->deleteBySelector($selector);
            $this->deleteRememberCookie($request);
            return;
        }

        $user = (new DeclUserModel())->find($userId);
        $auth = (new DeclUserAuthModel())->find($userId);

        if (!$user || !$auth) {
            $this->deleteRememberCookie($request);
            return;
        }

        session()->regenerate(true);
        session()->set([
            'decl_logged_in' => true,
            'decl_user_id' => (int) $user->id,
            'decl_must_change_password' => (bool) ($auth->must_change_password ?? false),
        ]);

        $ua = (string) $request->getUserAgent();
        $ip = (string) $request->getIPAddress();

        $new = $svc->createRememberMeToken((int) $user->id, $ua, $ip);
        $this->setRememberCookie($request, remember_pack($new['selector'], $new['validator']), (int) $new['expires']);

        (new DeclRememberTokenModel())->deleteBySelector($selector);
    }

    private function setRememberCookie(RequestInterface $request, string $value, int $expires): void
    {
        setcookie(
            remember_cookie_name(),
            $value,
            [
                'expires' => $expires,
                'path' => '/',
                'secure' => $request->isSecure(), // prod https -> true
                'httponly' => true,
                'samesite' => 'Lax',
            ]
        );
    }

    private function deleteRememberCookie(RequestInterface $request): void
    {
        setcookie(
            remember_cookie_name(),
            '',
            [
                'expires' => time() - 3600,
                'path' => '/',
                'secure' => $request->isSecure(),
                'httponly' => true,
                'samesite' => 'Lax',
            ]
        );
    }
}
