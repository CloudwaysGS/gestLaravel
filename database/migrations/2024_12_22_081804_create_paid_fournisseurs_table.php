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
        Schema::create('paid_fournisseurs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fournisseur_id')->nullable()->constrained('fournisseurs')->onDelete('cascade');
            $table->string('nom')->nullable();
            $table->decimal('montant', 10, 2);
            $table->decimal('reste', 10, 2);
            $table->float('depot')->nullable(); // état par défaut
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paid_fournisseurs');
    }
};
