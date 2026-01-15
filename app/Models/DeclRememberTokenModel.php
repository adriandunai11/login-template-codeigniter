<?php

namespace App\Models;

use CodeIgniter\Model;

class DeclRememberTokenModel extends Model
{
    protected $table = 'decl_remember_tokens';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'decl_user_id',
        'device_id',
        'selector',
        'validator_hash',
        'expires_at',
        'created_at',
        'last_used_at',
        'user_agent',
        'ip_address'
    ];

    public function findValidBySelector(string $selector): ?array
    {
        $row = $this->where('selector', $selector)->first();
        if (!$row)
            return null;

        if (strtotime($row['expires_at']) <= time())
            return null;

        return $row;
    }

    public function deleteBySelector(string $selector): void
    {
        $this->where('selector', $selector)->delete();
    }

    public function deleteExpired(): void
    {
        $this->where('expires_at <', date('Y-m-d H:i:s'))->delete();
    }

    public function deleteForUserDevice(int $userId, string $deviceId): void
    {
        $this->where('decl_user_id', $userId)
            ->where('device_id', $deviceId)
            ->delete();
    }

}
