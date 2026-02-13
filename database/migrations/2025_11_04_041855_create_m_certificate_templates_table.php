<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('m_certificate_templates', function (Blueprint $table) {
            $table->id('template_id');
            $table->string('template_name');
            $table->string('template_type')->unique(); // 'completion', 'participation', 'achievement', 'recognition'
            $table->string('template_file'); // PDF path
            $table->integer('name_x')->default(50);
            $table->integer('name_y')->default(120);
            $table->integer('course_x')->default(50);
            $table->integer('course_y')->default(150);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('m_certificate_templates');
    }
};
