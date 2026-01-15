<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class DeclUser extends Entity
{
    protected $attributes = [
        'status' => 'active',
    ];

    protected $casts = [
        'id' => 'integer',
        'intranet_user_id' => '?integer',
    ];

    public function isActive(): bool
    {
        return ($this->attributes['status'] ?? '') === 'active';
    }

}
