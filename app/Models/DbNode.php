<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DbNode extends Model
{
    protected $fillable = [
        'name',
        'host',
        'username',
        'password',
        'port',
    ];

    public function tenants()
    {
        return $this->hasMany(Tenant::class, 'db_node_id');
    }
}