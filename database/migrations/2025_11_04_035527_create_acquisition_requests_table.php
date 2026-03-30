<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acquisition_requests', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('author')->nullable();
            $table->string('isbn')->nullable();
            $table->string('publisher')->nullable();
            $table->integer('publication_year')->nullable();
            $table->text('reason')->nullable();
            $table->foreignId('requested_by')->constrained('members');
            $table->foreignId('branch_id')->constrained();
            $table->enum('status', ['pending', 'approved', 'rejected', 'ordered', 'received'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->decimal('estimated_price', 8, 2)->nullable();
            $table->integer('quantity')->default(1);
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acquisition_requests');
    }
};
