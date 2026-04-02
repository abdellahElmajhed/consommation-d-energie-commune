<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
     public function up(): void
    {
        Schema::create('compteurs', function (Blueprint $table) {
            $table->id();
            $table->integer('numero_contrat')->unique();
            $table->integer('numero_compteur');
            $table->string('address');
            $table->date('date_creation');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compteurs');
    }
};
