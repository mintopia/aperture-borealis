<?php

use App\Enums\DeviceCodeStatus;
use App\Models\Client;
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
        Schema::create('device_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(SocialProvider::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Client::class)->constrained()->cascadeOnDelete();
            $table->string('status')->default(DeviceCodeStatus::dcsPending);
            $table->timestamp('expires_at')->nullable();
            $table->string('device_code');
            $table->string('user_code');
            $table->longText('access_token')->nullable();
            $table->longText('refresh_token')->nullable();
            $table->timestamp('access_token_expires_at')->nullable();
            $table->string('external_id')->nullable();
            $table->string('email')->nullable();
            $table->string('nickname')->nullable();
            $table->string('avatar_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_codes');
    }
};
