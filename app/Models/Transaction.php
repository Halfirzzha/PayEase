<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $guarded = [];

    // Corrected method name for user relationship
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Corrected method name for department relationship
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
}