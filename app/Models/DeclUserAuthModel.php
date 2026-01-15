<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\DeclUserAuth;

class DeclUserAuthModel extends Model
{
    protected $table = 'decl_user_auth';
    protected $primaryKey = 'decl_user_id';

    protected $returnType = DeclUserAuth::class;

    protected $allowedFields = [
        'decl_user_id',
        'password_hash',
        'must_change_password',
        'password_changed_at',
        'failed_login_count',
        'locked_until',
        'last_login_at',
        'created_at',
        'updated_at'
    ];
    protected $useTimestamps = false;

    public function findByUserId(int $userId): ?DeclUserAuth
    {
        /** @var DeclUserAuth|null $auth */
        $auth = $this->where('decl_user_id', $userId)->first();
        return $auth ?: null;
    }
}
