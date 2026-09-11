<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sector')->nullable();          // Automotive, ICT, Hospitality...
            $table->string('website')->nullable();

            // Address
            $table->string('physical_address')->nullable();
            $table->string('postal_address')->nullable();
            $table->string('town')->nullable();
            $table->string('county')->nullable();

            // Primary contact person at the company
            $table->string('contact_person_name')->nullable();
            $table->string('contact_person_position')->nullable();
            $table->string('contact_person_phone')->nullable();
            $table->string('contact_person_email')->nullable();

            // Company-wide contact
            $table->string('company_phone')->nullable();
            $table->string('company_email')->nullable();

            // Metadata
            $table->enum('size', ['small', 'medium', 'large'])->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['sector', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};