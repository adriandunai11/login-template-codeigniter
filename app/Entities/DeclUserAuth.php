<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class DeclUserAuth extends Entity
{
    protected $casts = [
        'decl_user_id' => 'integer',
        'must_change_password' => 'boolean',
        'failed_login_count' => 'integer',
    ];

    public function isLocked(): bool
    {
        $lockedUntil = $this->attributes['locked_until'] ?? null;
        if (empty($lockedUntil))
            return false;
        return strtotime((string) $lockedUntil) > time();
    }

    public function mustChangePassword(): bool
    {
        return (bool) ($this->attributes['must_change_password'] ?? false);
    }

    public function lockRemainingMinutes(): ?int
    {
        if (!$this->locked_until) {
            return null;
        }

        $remaining = strtotime($this->locked_until) - time();
        if ($remaining <= 0) {
            return null;
        }

        return (int) ceil($remaining / 60);
    }

    public function lockRemainingSeconds(): ?int
    {
        if (!$this->locked_until) {
            return null;
        }

        $remaining = strtotime($this->locked_until) - time();
        return $remaining > 0 ? $remaining : null;
    }
}
