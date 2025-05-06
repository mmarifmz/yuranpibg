<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Family extends Model
{
    // optionally define fillable or guarded attributes
    protected $guarded = [];

    public static function getStudentsByFamilyId($familyId)
    {
        return self::where('family_id', $familyId)
            ->whereNotNull('student_name')
            ->get();
    }
}

