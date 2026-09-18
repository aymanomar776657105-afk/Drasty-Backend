<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $table = 'courses';
    protected $primaryKey = 'course_id';

    protected $fillable = [
        'course_code',
        'course_name_ar',
        'course_name_en',
        'credit_hours',
    ];

    public function courseOfferings()
    {
        return $this->hasMany(CourseOffering::class, 'course_id', 'course_id');
    }
}