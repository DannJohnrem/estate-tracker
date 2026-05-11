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
        Schema::create('lots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();

            $table->string('lot_number');
            $table->string('block_number')->nullable();
            $table->string('subdivision');
            $table->string('phase')->nullable();
            $table->decimal('lot_area', 8, 2);

            $table->decimal('total_contract_price', 12, 2);
            $table->decimal('down_payment', 12, 2)->default(0);
            $table->decimal('monthly_amortization', 10, 2);
            $table->integer('term_months');
            $table->integer('months_paid')->default(0);
            $table->date('start_date');
            $table->date('next_due_date')->nullable();

            $table->enum('status', ['active', 'delinquent', 'fully_paid', 'cancelled'])
                ->default('active');

            $table->timestamps();
            $table->softDeletes();

            $table->index('client_id');
            $table->index('status');
            $table->index('next_due_date');
            $table->index(['client_id', 'status']);
            $table->index(['next_due_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lots');
    }
};
