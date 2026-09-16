<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $table = 'departments';
    protected $primaryKey = 'department_id';

    protected $fillable = [
        'name',
        'code',
    ];

    public function doctors()
    {
        return $this->hasMany(Doctor::class, 'department_id', 'department_id');
    }
}