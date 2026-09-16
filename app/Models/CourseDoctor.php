<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseDoctor extends Model
{
    use HasFactory;

    protected $table = 'course_doctors';
    protected $primaryKey = 'course_doctor_id';
    
    // يحتوي الجدول في المخطط على created_at فقط بدون updated_at
    const UPDATED_AT = null;

    protected $fillable = [
        'offering_id',
        'doctor_id',
    ];

    public function offering()
    {
        return $this->belongsTo(CourseOffering::class, 'offering_id', 'offering_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'doctor_id');
    }
}