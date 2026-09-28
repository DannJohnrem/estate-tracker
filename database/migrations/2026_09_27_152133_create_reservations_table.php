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
        Schema::create('reservations', function (Blueprint $table) {
             $table->uuid('id')->primary();
            $table->foreignUuid('client_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('agent_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('project_id')->nullable()->constrained()->nullOnDelete();

            // Lot details as plain fields — no Lot row exists yet at reservation stage
            $table->string('lot_number');
            $table->string('block_number')->nullable();
            $table->string('subdivision');
            $table->string('phase')->nullable();
            $table->decimal('lot_area', 8, 2)->nullable();
            $table->decimal('reservation_fee', 12, 2)->nullable();
            $table->date('reservation_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'expired', 'cancelled'])
                ->default('pending');

            // Set once "Confirm" creates the real Lot — keeps the audit trail
            $table->foreignUuid('converted_lot_id')->nullable()
                ->constrained('lots')->nullOnDelete();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('expiry_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
