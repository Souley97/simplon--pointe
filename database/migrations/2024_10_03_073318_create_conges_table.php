<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCongesTable extends Migration
{
    public function up()
    {
        Schema::create('conges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->enum('type', ['congé', 'permission']);
            $table->text('motif')->nullable();
            $table->date('date_debut');
            $table->date('date_fin')->nullable();
            $table->enum('status', ['en attente', 'approuvée', '    '])->default('en attente');
            $table->timestamps();

            // Foreign key to users
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('conges');
    }
}
