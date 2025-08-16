<?php
// app/Models/Issue.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Issue extends Model
{
    use HasFactory;

    protected $fillable = [
        'building',
        'room', 
        'equipment',
        'device',
        'description',
        'email',
        'image_path',
        'status',
        'admin_reply'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];
}