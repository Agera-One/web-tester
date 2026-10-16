<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestRunsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('test_runs', function (Blueprint $table) {
            $table->id();
            $table->enum('triggered_by', ['manual', 'schedule']);
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->unsignedBigInteger('schedule_id')->nullable();
            $table->foreignId('environment_id')->constrained('environments')->onDelete('cascade');
            $table->string('browser')->default('chromium');
            $table->text('scope')->nullable();
            $table->enum('status', ['queued', 'running', 'passed', 'failed', 'cancelled', 'error'])->default('queued');
            $table->integer('total')->default(0);
            $table->integer('passed')->default(0);
            $table->integer('failed')->default(0);
            $table->integer('skipped')->default(0);
            $table->integer('flaky')->default(0);
            $table->integer('duration_ms')->nullable();
            $table->integer('pid')->nullable();
            $table->string('log_path')->nullable();
            $table->string('report_path')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
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
        Schema::dropIfExists('test_runs');
    }
}
