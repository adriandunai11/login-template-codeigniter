<?php

namespace App\Services;

use App\Models\DeclUserModel;
use App\Models\DeclUserAuthModel;
use App\Entities\DeclUser;
use App\Entities\DeclUserAuth;
use App\Models\DeclRememberTokenModel;


class DeclAuthService
{
    public function __construct(
        private readonly DeclUserModel $users,
        private readonly DeclUserAuthModel $auths,
        private readonly DeclRememberTokenModel $rememberTokens
    ) {
    }

    /**
     * @return array{ok:bool, error?:string, user?:DeclUser, auth?:DeclUserAuth}
     */
    public function attemptLogin(string $antraId, string $password): array
    {
        $antraId = trim($antraId);
        if ($antraId === '' || $password === '') {
            return ['ok' => false, 'error' => 'Hiányzó azonosító vagy jelszó.'];
        }

        $user = $this->users->findActiveByAntraId($antraId);
        if (!$user) {
            return ['ok' => false, 'error' => 'Hibás azonosító vagy jelszó.'];
        }

        $auth = $this->auths->findByUserId((int) $user->id);
        if (!$auth) {
            return ['ok' => false, 'error' => 'Hibás azonosító vagy jelszó.'];
        }

        if ($auth->isLocked()) {
            $minutes = $auth->lockRemainingMinutes();

            if ($minutes !== null) {
                return [
                    'ok' => false,
                    'error' => "A fiók ideiglenesen zárolva van. Próbáld újra {$minutes} perc múlva."
                ];
            }

            return [
                'ok' => false,
                'error' => 'A fiók ideiglenesen zárolva van. Próbáld később.'
            ];
        }

        if (!password_verify($password, (string) $auth->password_hash)) {
            $this->registerFailedLogin((int) $user->id, $auth);
            return ['ok' => false, 'error' => 'Hibás azonosító vagy jelszó.'];
        }

        $this->registerSuccessfulLogin((int) $user->id);

        return ['ok' => true, 'user' => $user, 'auth' => $auth];
    }

    public function changePassword(int $userId, string $currentPassword, string $newPassword): array
    {
        $auth = $this->auths->findByUserId($userId);
        if (!$auth)
            return ['ok' => false, 'error' => 'Hitelesítési rekord nem található.'];

        if ($currentPassword === '' || !password_verify($currentPassword, (string) $auth->password_hash)) {
            return ['ok' => false, 'error' => 'A jelenlegi jelszó hibás.'];
        }

        if (strlen($newPassword) < 10) {
            return ['ok' => false, 'error' => 'Az új jelszó legalább 10 karakter legyen.'];
        }

        $this->auths->update($userId, [
            'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
            'must_change_password' => 0,
            'password_changed_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return ['ok' => true];
    }

    private function registerFailedLogin(int $userId, DeclUserAuth $auth): void
    {
        $failed = ((int) $auth->failed_login_count) + 1;

        $update = [
            'failed_login_count' => $failed,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // policy: 5 próbálkozás -> 15 perc lock
        if ($failed >= 5) {
            $update['locked_until'] = date('Y-m-d H:i:s', time() + 15 * 60);
            $update['failed_login_count'] = 0;
        }

        $this->auths->update($userId, $update);
    }

    private function registerSuccessfulLogin(int $userId): void
    {
        $this->auths->update($userId, [
            'failed_login_count' => 0,
            'locked_until' => null,
            'last_login_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function createRememberMeToken(int $userId, string $deviceId, string $userAgent = '', string $ip = ''): array
    {
        $this->rememberTokens->deleteForUserDevice($userId, $deviceId);

        $selector = bin2hex(random_bytes(12));
        $validator = bin2hex(random_bytes(32));
        $validatorHash = hash('sha256', $validator);

        $expiresAtTs = time() + 30 * 24 * 60 * 60;
        $expiresAt = date('Y-m-d H:i:s', $expiresAtTs);

        $this->rememberTokens->insert([
            'decl_user_id' => $userId,
            'device_id' => $deviceId,
            'selector' => $selector,
            'validator_hash' => $validatorHash,
            'expires_at' => $expiresAt,
            'created_at' => date('Y-m-d H:i:s'),
            'last_used_at' => null,
            'user_agent' => substr($userAgent, 0, 255),
            'ip_address' => substr($ip, 0, 64),
        ]);

        return ['selector' => $selector, 'validator' => $validator, 'expires' => $expiresAtTs];
    }

    public function consumeRememberMe(string $selector, string $validator): ?int
    {
        $row = $this->rememberTokens->findValidBySelector($selector);
        if (!$row) {
            return null;
        }

        $givenHash = hash('sha256', $validator);
        $expectedHash = (string) ($row['validator_hash'] ?? '');

        if (!hash_equals($expectedHash, $givenHash)) {
            $this->rememberTokens->deleteBySelector($selector);
            return null;
        }

        $this->rememberTokens->update((int) $row['id'], [
            'last_used_at' => date('Y-m-d H:i:s'),
        ]);

        return (int) $row['decl_user_id'];
    }

}
