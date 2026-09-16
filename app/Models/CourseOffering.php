<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseOffering extends Model
{
    use HasFactory;

    protected $table = 'course_offerings';
    protected $primaryKey = 'offering_id';

    protected $fillable = [
        'course_id',
        'semester_id',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id', 'course_id');
    }

    public function courseDoctors()
    {
        return $this->hasMany(CourseDoctor::class, 'offering_id', 'offering_id');
    }
}