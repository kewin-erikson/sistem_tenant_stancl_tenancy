<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class DbNode extends Model
{
    protected $table = 'db_nodes';
    protected $fillable = ['name', 'host', 'username', 'password', 'port', 'is_active'];

    public function tenants()
    {
        return $this->hasMany(Tenant::class, 'db_node_id');
    }
}