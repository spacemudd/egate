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
        Schema::create('egate_requests', function (Blueprint $table) {
            $table->id();
            $table->string('method')->comment('Request method: GetStatus, SearchCardAcs');
            $table->string('type')->nullable()->comment('Data type: 0=Card, 1=QRCode, 2=PIN, 3=Button, 5=Alarm, 9=Base64, 10=Fingerprint, 11=Vein, 12=RFID, 13=Face, 23=Face, 28=JSON, 30=WG66, 31=Social Security Card');
            $table->string('serial')->nullable()->comment('Controller serial number');
            $table->string('device_id')->nullable()->comment('Custom device identifier');
            $table->string('mac_address')->nullable()->comment('Device MAC address');
            $table->string('ip_address')->nullable()->comment('Device IP address');
            $table->string('reader')->nullable()->comment('Reading head: 0=in, 1=out');
            $table->string('source')->nullable()->comment('Data source: 0=WG reader, 1=RS232, 2=485, 5=USB, 6=232 converter, 7=Button, 9=Network');
            $table->string('status')->nullable()->comment('Current gate state');
            $table->string('input')->nullable()->comment('Input status');
            $table->string('card')->nullable()->comment('Card number, password, QR code, etc.');
            $table->text('data')->nullable()->comment('Additional data (fingerprint, face, vein, etc.)');
            $table->string('index')->nullable()->comment('Random value');
            $table->string('key')->nullable()->comment('Key value for heartbeat');
            $table->string('now')->nullable()->comment('Device time');
            $table->string('crc')->nullable()->comment('CRC value');
            $table->string('t1')->nullable()->comment('Temperature 1');
            $table->string('h1')->nullable()->comment('Humidity 1');
            $table->string('t2')->nullable()->comment('Temperature 2');
            $table->string('h2')->nullable()->comment('Humidity 2');
            $table->string('next_num')->nullable()->comment('Next number');
            $table->string('ver')->nullable()->comment('Firmware version');
            $table->string('will_pass')->nullable()->comment('How many people still need to pass');
            $table->string('passed')->nullable()->comment('How many people have already passed');
            $table->text('modbus')->nullable()->comment('Modbus data');
            $table->string('order_code')->nullable()->comment('Order number for business');
            $table->json('request_data')->nullable()->comment('Full request data as JSON');
            $table->json('response_data')->nullable()->comment('Response data sent back');
            $table->string('response_status')->nullable()->comment('Response status: 0=reject, 1=open, 2=alarm, 3=close, 4=ignore');
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['method', 'created_at']);
            $table->index(['serial', 'created_at']);
            $table->index(['type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('egate_requests');
    }
};
