<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Environment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'url',
        'username',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    // Tambahkan fungsi ini di dalam kelas Environment
    public function testScenarios()
    {
        return $this->hasMany(TestScenario::class);
    }
}