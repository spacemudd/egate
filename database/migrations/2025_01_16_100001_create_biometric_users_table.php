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
        Schema::create('biometric_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('biometric_devices')->onDelete('cascade');
            $table->string('device_user_id')->comment('User ID from the biometric device');
            $table->string('name')->comment('Full name of the user');
            $table->string('employee_id')->nullable()->comment('Employee ID or badge number');
            $table->string('department')->nullable()->comment('Department or division');
            $table->string('position')->nullable()->comment('Job position or title');
            $table->string('card_number')->nullable()->comment('Card number if applicable');
            $table->string('fingerprint_template')->nullable()->comment('Fingerprint template data');
            $table->string('face_template')->nullable()->comment('Face recognition template data');
            $table->json('biometric_data')->nullable()->comment('Additional biometric data');
            $table->boolean('is_active')->default(true)->comment('Whether user is active');
            $table->timestamp('last_sync')->nullable()->comment('Last sync timestamp');
            $table->text('notes')->nullable()->comment('Additional notes about the user');
            $table->timestamps();
            
            // Unique constraint to prevent duplicate users on same device
            $table->unique(['device_id', 'device_user_id']);
            
            // Indexes for better performance
            $table->index(['device_id']);
            $table->index(['device_user_id']);
            $table->index(['name']);
            $table->index(['employee_id']);
            $table->index(['is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biometric_users');
    }
};
