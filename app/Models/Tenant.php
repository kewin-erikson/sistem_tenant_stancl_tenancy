<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    protected $fillable = [
        'id', 
        'tenancy_db_name', 
        'db_node_id', 
        'is_existing_db', 
        'user_limit',
    ];

    protected $casts = [
        'is_existing_db' => 'boolean',
        'user_limit' => 'integer',
    ];

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'db_node_id',
            'is_existing_db',
            'user_limit',
        ];
    }

    public function db_node()
    {
        return $this->belongsTo(DbNode::class, 'db_node_id');
    }

    public function hasReachedUserLimit(): bool
    {
        return $this->run(function () {
            $userCount = \App\Models\User::count();
            return $userCount >= $this->user_limit;
        });
    }

    public function getRemainingUsersAttribute(): int
    {
        return $this->run(function () {
            $userCount = \App\Models\User::count();
            return max(0, $this->user_limit - $userCount);
        });
    }
}