<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('test_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('cron_expression');
            $table->text('scope');
            $table->foreignId('environment_id')->nullable()->constrained('environments')->onDelete('set null');
            $table->string('browser')->default('chromium');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_run_at')->nullable();
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
        Schema::dropIfExists('test_schedules');
    }
}
