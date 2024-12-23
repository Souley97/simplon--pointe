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
        Schema::create('pointages', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['present', 'absence', 'retard']);
        $table->string('motif')->nullable();
        $table->date('date');
        $table->time('heure_present')->nullable();
        $table->time('heure_depart')->nullable();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        // create by
        $table->unsignedBigInteger('created_by')->nullable();

        // foreign key
        $table->foreign('created_by')->references('id')->on('users');
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pointages');
    }
};
