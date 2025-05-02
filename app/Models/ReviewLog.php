<?php
// app/Models/ReviewLog.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'family_id',
        'ip',
        'user_agent',
    ];
}
