<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\DeclUser;

class DeclUserModel extends Model
{
    protected $table = 'decl_users';
    protected $primaryKey = 'id';

    protected $returnType = DeclUser::class;

    protected $allowedFields = [
        'intranet_user_id',
        'antra_id',
        'full_name',
        'email',
        'phone',
        'status',
        'created_at',
        'updated_at'
    ];
    protected $useTimestamps = false;

    public function findActiveByAntraId(string $antraId): ?DeclUser
    {
        /** @var DeclUser|null $user */
        $user = $this->where('antra_id', $antraId)
            ->where('status', 'active')
            ->first();

        return $user ?: null;
    }
}
