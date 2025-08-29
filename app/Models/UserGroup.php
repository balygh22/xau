<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserGroup extends Model
{
    use HasFactory;

    protected $table = 'usergroups'; // map to UserGroups (MySQL is case-insensitive)
    protected $primaryKey = 'GroupID';
    public $timestamps = false;

    protected $fillable = [
        'GroupName',
    ];

    // Many-to-many: UserGroups <-> Permissions via GroupPermissions
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'grouppermissions', 'GroupID', 'PermissionID');
    }
}