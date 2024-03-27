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
        Schema::create('letters', function (Blueprint $table) {
            $table->id();
            $table->date('received_date')->nullable();
            $table->string('letters_type');
            $table->string('reference_number')->nullable()->unique()->comment('Nomor Surat');
            $table->date('letter_date')->nullable();
            $table->string('from')->nullable();
            $table->text('description')->nullable();
            $table->date('disposition_date')->nullable();
            $table->text('disposition_note')->nullable();
            $table->text('disposition_process')->nullable();
            $table->string('status')->nullable();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate();
            $table->string('read_status')->nullable();
            $table->timestamps();
            
          
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('letters');
    }
};
