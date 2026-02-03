<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('domain_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_id')->unique();
            $table->string('event_name')->index();
            $table->string('aggregate_id')->index();
            $table->json('payload');
            $table->dateTime('occurred_on');
            $table->unsignedInteger('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domain_events');
    }
};
