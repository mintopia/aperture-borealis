<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('themes');
    }

    public function down(): void
    {
        Schema::create('themes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->boolean('readonly')->default(false);
            $table->boolean('active')->default(false);
            $table->boolean('dark_mode')->default(false);
            $table->string('primary');
            $table->string('secondary')->nullable();
            $table->string('nav_background');
            $table->longText('css');
            $table->timestamps();
        });
    }
};
