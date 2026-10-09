<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestScenario extends Model
{
    use HasFactory;

    protected $fillable = [
        'environment_id',
        'name',
        'description',
        'steps',
        'expected_result',
    ];

    // Secara otomatis mengubah string JSON dari basis data menjadi Array PHP, dan sebaliknya
    protected $casts = [
        'steps' => 'array',
    ];

    // Relasi: Setiap skenario dimiliki oleh satu environment
    public function environment()
    {
        return $this->belongsTo(Environment::class);
    }
}