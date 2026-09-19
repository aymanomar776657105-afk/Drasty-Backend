<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;

    protected $table = 'doctors';
    protected $primaryKey = 'doctor_id';
    public $timestamps = false; // فعّلها إذا كان جدول doctors يحتوي على created_at و updated_at

  protected $fillable = [
    'full_name',
    'name_ar',
    'name_en',
    'email',
    'password',
    'role_id',
    'status',
];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'department_id');
    }

    public function courseDoctors()
    {
        return $this->hasMany(CourseDoctor::class, 'doctor_id', 'doctor_id');
    }

    public function contents()
    {
        return $this->hasMany(Content::class, 'doctor_id', 'doctor_id');
    }
}