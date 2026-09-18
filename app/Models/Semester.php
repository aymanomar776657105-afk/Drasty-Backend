<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    use HasFactory;

    protected $table = 'semesters';
    protected $primaryKey = 'semester_id';

    public $timestamps = false;

    protected $fillable = [
        'level_id',
        'name',
        'semester_number',
        'academic_year',
    ];

    // علاقة الفصل بالمستوى الدراسي
    public function level()
    {
        return $this->belongsTo(Level::class, 'level_id', 'level_id');
    }

    // علاقة الفصل بالمواد المطروحة فيه
    public function courseOfferings()
    {
        return $this->hasMany(CourseOffering::class, 'semester_id', 'semester_id');
    }
}