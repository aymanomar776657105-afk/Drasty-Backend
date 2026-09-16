<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    use HasFactory;

    protected $table = 'semesters';
    protected $primaryKey = 'semester_id';

    const UPDATED_AT = null; // الجدول لا يحتوي على updated_at

    protected $fillable = [
        'level_id',
        'name',
        'semester_number',
        'academic_year',
    ];

    public function level()
    {
        return $this->belongsTo(Level::class, 'level_id', 'level_id');
    }
}