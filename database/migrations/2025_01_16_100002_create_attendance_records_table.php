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
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('biometric_devices')->onDelete('cascade');
            $table->foreignId('biometric_user_id')->constrained('biometric_users')->onDelete('cascade');
            $table->string('device_user_id')->comment('User ID from device (for reference)');
            $table->timestamp('punch_time')->comment('Punch in/out timestamp');
            $table->string('punch_type')->comment('Punch type: in, out, break_in, break_out');
            $table->string('verify_mode')->nullable()->comment('Verification method: fingerprint, card, face, etc.');
            $table->string('status')->nullable()->comment('Punch status from device');
            $table->string('work_code')->nullable()->comment('Work code if applicable');
            $table->json('device_data')->nullable()->comment('Raw data from device');
            $table->boolean('is_processed')->default(false)->comment('Whether record has been processed');
            $table->timestamp('processed_at')->nullable()->comment('When record was processed');
            $table->text('notes')->nullable()->comment('Additional notes');
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['device_id']);
            $table->index(['biometric_user_id']);
            $table->index(['punch_time']);
            $table->index(['punch_type']);
            $table->index(['is_processed']);
            $table->index(['device_id', 'punch_time']);
            $table->index(['biometric_user_id', 'punch_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
