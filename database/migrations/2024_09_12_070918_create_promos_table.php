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
        Schema::create('promos', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->date('date_debut');
            $table->date('date_fin');
            $table->enum('statut', ['encours', 'termine']);

            // heur horaires pointage
            $table->time( 'horaire');
            $table->foreignId('formateur_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('chef_projet_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('assistant_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('fabrique_id')->constrained()->onDelete('cascade');
            $table->foreignId('formation_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promos');
    }
};
