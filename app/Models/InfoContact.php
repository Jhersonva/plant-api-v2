<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfoContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'location', 
        'cellphone', 
        'email', 
        'attention_hours'
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
