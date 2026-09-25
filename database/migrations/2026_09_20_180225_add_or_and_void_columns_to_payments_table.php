<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Official receipt number; nullable so existing rows stay valid, unique when filled
            $table->string('or_number')->nullable()->unique()->after('lot_id');

            // GCash / bank / check reference
            $table->string('reference_number')->nullable()->after('method');

            // How many monthly installments this payment advanced the lot (used when voiding)
            $table->unsignedSmallInteger('months_covered')->default(0)->after('amount');

            // Void instead of delete: keeps the audit trail
            $table->string('status')->default('posted')->index()->after('notes');
            $table->timestamp('voided_at')->nullable()->after('status');
            $table->foreignIdFor(User::class, 'voided_by')
                ->nullable()
                ->after('voided_at')
                ->constrained('users')
                ->nullOnDelete();
            $table->text('void_reason')->nullable()->after('voided_by');
        });

        // Backfill months_covered for existing payments (same rule as RecordPayment)
        DB::table('payments')
            ->join('lots', 'lots.id', '=', 'payments.lot_id')
            ->select('payments.id', 'payments.amount', 'lots.monthly_amortization')
            ->orderBy('payments.id')
            ->chunk(500, function ($rows) {
                foreach ($rows as $row) {
                    $months = $row->monthly_amortization > 0
                        ? (int) floor(round($row->amount / $row->monthly_amortization, 6))
                        : 0;

                    DB::table('payments')->where('id', $row->id)->update(['months_covered' => $months]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['voided_by']);
            $table->dropUnique(['or_number']);
            $table->dropIndex(['status']);
            $table->dropColumn([
                'or_number',
                'reference_number',
                'months_covered',
                'status',
                'voided_at',
                'voided_by',
                'void_reason',
            ]);
        });
    }
};
