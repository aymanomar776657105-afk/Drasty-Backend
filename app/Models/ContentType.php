<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentType extends Model
{
    use HasFactory;

    // 1. تحديد اسم الجدول في قاعدة البيانات
    protected $table = 'content_types';

    // 2. تعيين المفتاح الأساسي (لأن الافتراضي في لارافيل هو id)
    protected $primaryKey = 'content_type_id';

    // 3. تعطيل التوقيت الزمني إذا كان جدول التصنيفات لا يحتوي على created_at و updated_at
    // (إذا كانت موجودة في الميجريشن، احذف هذا السطر)
    public $timestamps = false;

    // 4. الأعمدة المسموح تعبئتها جماعياً
    protected $fillable = [
        'type_name',
    ];

    // 5. العلاقة مع جدول المحتويات (نوع المحتوى الواحد يتبعه عدة ملفات)
    public function contents()
    {
        return $this->hasMany(Content::class, 'content_type_id', 'content_type_id');
    }
}