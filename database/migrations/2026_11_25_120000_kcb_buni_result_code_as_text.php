<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// KCB sometimes answers a PIN prompt with a code like "E3008"; the integer column made saving it crash
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kcb_buni_transactions', function (Blueprint $table) {
            $table->string('result_code', 20)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('kcb_buni_transactions', function (Blueprint $table) {
            $table->integer('result_code')->nullable()->change();
        });
    }
};
