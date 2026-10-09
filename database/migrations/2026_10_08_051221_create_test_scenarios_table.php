<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestScenariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('test_scenarios', function (Blueprint $table) {
            $table->id();
            // Menautkan skenario dengan environment target
            $table->foreignId('environment_id')->constrained('environments')->onDelete('cascade');
            
            $table->string('name'); // Nama pengujian (contoh: "Login Sukses")
            $table->text('description')->nullable(); // Deskripsi pengujian
            $table->json('steps')->nullable(); // Menyimpan langkah-langkah dalam format JSON
            $table->text('expected_result')->nullable(); // Hasil yang diharapkan
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('test_scenarios');
    }
}
