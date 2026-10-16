<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestRunHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('test_run_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_scenario_id')->constrained()->cascadeOnDelete();
            $table->string('status');
            $table->longText('log_output');
            $table->string('screenshot_path')->nullable();
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
        Schema::dropIfExists('test_run_histories');
    }
}
