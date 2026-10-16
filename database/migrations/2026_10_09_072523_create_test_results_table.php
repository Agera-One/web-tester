<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('test_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_run_id')->constrained('test_runs')->onDelete('cascade');
            $table->foreignId('test_case_id')->nullable()->constrained('test_cases')->onDelete('set null');
            $table->string('title');
            $table->enum('status', ['passed', 'failed', 'skipped', 'timedOut', 'flaky']);
            $table->integer('duration_ms')->nullable();
            $table->integer('retry')->default(0);
            $table->text('error_message')->nullable();
            $table->longText('error_stack')->nullable();
            $table->string('screenshot_path')->nullable();
            $table->string('video_path')->nullable();
            $table->string('trace_path')->nullable();
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
        Schema::dropIfExists('test_results');
    }
}
