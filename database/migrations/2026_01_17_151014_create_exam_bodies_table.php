<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
       // 05_exam_bodies.php (NEW)
Schema::create('exam_bodies', function (Blueprint $table) {
    $table->id();
    $table->string('name'); // "CDACC", "NITA", "KNEC", "City & Guilds"
    $table->string('code')->unique(); // "CDACC", "NITA", "KNEC"
    $table->text('description')->nullable();
    $table->string('website')->nullable();
    $table->string('contact_person')->nullable();
    $table->string('contact_email')->nullable();
    $table->string('contact_phone')->nullable();
    $table->boolean('is_active')->default(true);
    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
    $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamps();
    $table->softDeletes();

    $table->index('code');
    $table->index('is_active');
});
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_bodies');
    }
};
