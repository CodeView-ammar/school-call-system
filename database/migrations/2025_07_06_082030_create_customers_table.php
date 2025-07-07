<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('cust_name_ar')->nullable();
            $table->string('cust_name_en')->nullable();
            $table->string('cust_vat_no')->nullable();
            $table->string('cust_tr_no')->nullable();
            $table->string('cust_tel')->nullable();
            $table->string('cust_email')->nullable();
            $table->string('cust_mail_password')->nullable();
            $table->string('cust_smtp_server')->nullable();
            $table->string('cust_port_no')->nullable();
            $table->string('cust_email_user')->nullable();
            $table->string('cust_mobile_no')->nullable();
            $table->string('cust_code')->unique();
            $table->text('cust_logo')->nullable();
            $table->string('cust_sms_gateway')->nullable();
            $table->string('cust_sms_sender_name')->nullable();
            $table->string('cust_sms_user')->nullable();
            $table->string('cust_sms_password')->nullable();
            $table->integer('cust_stud_no')->nullable();
            $table->integer('cust_branchcount')->nullable();
            $table->string('cust_isactive', 10)->default('1');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};