<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 06_exam_types.php (NEW - Depends on exam_bodies)
Schema::create('exam_types', function (Blueprint $table) {
    $table->id();
    $table->foreignId('exam_body_id')->constrained('exam_bodies')->onDelete('cascade');
    $table->string('name'); // "Artisan", "Certificate", "Diploma", "Higher Diploma"
    $table->string('code'); // "ART", "CERT", "DIP", "HDIP"
    $table->integer('level')->nullable(); // NQF level: 1,2,3,4
    $table->integer('duration_months')->nullable();
    $table->text('description')->nullable();
    $table->boolean('is_active')->default(true);
    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
    $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamps();
    $table->softDeletes();

    $table->index('code');
    $table->index('exam_body_id');
    $table->index('is_active');
    $table->index('level');
    $table->unique(['exam_body_id', 'code']);
    $table->unique(['exam_body_id', 'name']);
});
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_types');
    }
};
