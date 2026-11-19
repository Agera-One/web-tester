<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestBuilder extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = ['steps' => 'array'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
