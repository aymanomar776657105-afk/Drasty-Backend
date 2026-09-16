<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    use HasFactory;

    protected $table = 'levels';
    protected $primaryKey = 'level_id';

    const UPDATED_AT = null; // الجدول لا يحتوي على updated_at

    protected $fillable = [
        'department_id',
        'name',
        'level_number',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'department_id');
    }

    public function semesters()
    {
        return $this->hasMany(Semester::class, 'level_id', 'level_id');
    }
}