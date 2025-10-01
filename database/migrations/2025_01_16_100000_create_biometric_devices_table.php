<?php

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
        Schema::create('biometric_devices', function (Blueprint $table) {
            $table->id();
            $table->string('serial_number')->unique()->comment('Device serial number');
            $table->string('device_name')->nullable()->comment('Custom device name');
            $table->string('device_type')->default('zkteco')->comment('Device type (zkteco, etc.)');
            $table->string('ip_address')->nullable()->comment('Device IP address');
            $table->string('mac_address')->nullable()->comment('Device MAC address');
            $table->string('firmware_version')->nullable()->comment('Device firmware version');
            $table->string('language')->nullable()->comment('Device language setting');
            $table->string('push_version')->nullable()->comment('Push version for sync');
            $table->json('device_options')->nullable()->comment('Device configuration options');
            $table->string('status')->default('offline')->comment('Device status: online, offline, error');
            $table->timestamp('last_seen')->nullable()->comment('Last communication timestamp');
            $table->text('notes')->nullable()->comment('Additional notes about the device');
            $table->boolean('is_active')->default(true)->comment('Whether device is active');
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['serial_number']);
            $table->index(['status']);
            $table->index(['is_active']);
            $table->index(['last_seen']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biometric_devices');
    }
};
