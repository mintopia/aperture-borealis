<?php

use App\Models\SocialProvider;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('social_provider_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(SocialProvider::class)->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code');
            $table->string('description')->nullable();
            $table->string('type');
            $table->boolean('encrypted')->default(false);
            $table->boolean('hidden')->default(false);
            $table->string('validation')->nullable();
            $table->longText('value')->nullable();
            $table->integer('order');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_provider_settings');
    }
};
