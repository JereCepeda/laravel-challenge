<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'slug',
        'description',
        'permissions',
        'is_active',
    ];
    
    protected $casts = [
        'permissions' => 'array',
        'is_active' => 'boolean',
    ];
    
    public function users()
    {
        return $this->hasMany(User::class);
    }
    public function getPermissionsAttribute($value)
    {
        return json_decode($value, true);
    }
    public function hasPermission($permission)
    {
        $permissions = $this->permissions ?? [];
        return in_array($permission, $permissions);
    }
}
