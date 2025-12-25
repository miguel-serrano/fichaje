<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\App;

return new class extends Migration {
    public function up(): void {
        // Only create this table in testing environment
        if (App::environment('testing') || App::runningUnitTests()) {
            Schema::create('users_tests', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('email')->unique();
                $table->string('name');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }
    public function down(): void {
        // Only drop if in testing environment
        if (App::environment('testing') || App::runningUnitTests()) {
            Schema::dropIfExists('users_tests');
        }
    }
};

