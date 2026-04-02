<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consommation', function (Blueprint $table) {
            $table->id();
            $table->integer('numero_contrat');
            $table->integer('c_kwh');
            $table->integer('c_dhs');
            $table->timestamps();
           $table->foreign('numero_contrat')->references('numero_contrat')->on('compteurs')->onDelete('cascade');
        });
        
    }

    public function down(): void
    {
        Schema::dropIfExists('consommation');
    }
};
 
