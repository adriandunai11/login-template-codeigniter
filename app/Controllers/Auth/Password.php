<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RedirectResponse;

class Password extends BaseController
{
    public function change()
    {
        if (!session()->get('decl_logged_in')) {
            return redirect()->to('/auth/login');
        }

        return view('auth/change_password', [
            'title' => 'Jelszócsere',
            'error' => session()->getFlashdata('error'),
        ]);
    }

    public function update(): RedirectResponse
    {
        if (!session()->get('decl_logged_in')) {
            return redirect()->to('/auth/login');
        }

        $current = (string) $this->request->getPost('current_password');
        $new1 = (string) $this->request->getPost('new_password');
        $new2 = (string) $this->request->getPost('new_password_confirm');

        if ($new1 === '' || $new1 !== $new2) {
            return redirect()->to('/auth/password/change')
                ->with('error', 'Az új jelszavak nem egyeznek.');
        }

        $svc = service('declAuthService');
        $userId = (int) session()->get('decl_user_id');

        $result = $svc->changePassword($userId, $current, $new1);
        if (!($result['ok'] ?? false)) {
            return redirect()->to('/auth/password/change')
                ->with('error', $result['error'] ?? 'Nem sikerült a jelszócsere.');
        }

        session()->set('decl_must_change_password', false);
        return redirect()->to('/');
    }
}
