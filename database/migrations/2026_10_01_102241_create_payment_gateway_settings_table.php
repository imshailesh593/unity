<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_gateway_settings', function (Blueprint $table) {
            $table->id();
            $table->string('gateway')->unique()->default('phonepe');

            $table->string('client_id')->nullable();
            $table->text('client_secret')->nullable();
            $table->string('client_version')->nullable();

            $table->string('sandbox_client_id')->nullable();
            $table->text('sandbox_client_secret')->nullable();
            $table->string('sandbox_client_version')->nullable();

            $table->string('webhook_username')->nullable();
            $table->text('webhook_password')->nullable();

            $table->boolean('is_sandbox')->default(true);
            $table->boolean('is_active')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateway_settings');
    }
};
