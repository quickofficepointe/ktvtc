<?php
// database/migrations/xxxx_xx_xx_create_certifications_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('certifications', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('issuing_body');
            $table->string('logo_path')->nullable();
            $table->string('certificate_number')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->enum('certification_type', [
                'accreditation',
                'examination_body',
                'professional_body',
                'registration'
            ])->default('accreditation');
            $table->string('website')->nullable();

            // Audit fields
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('certification_type');
            $table->index('is_active');
        });
    }

    public function down()
    {
        Schema::dropIfExists('certifications');
    }
};
