<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['client_id', 'agent_id', 'project_id', 'lot_number', 'block_number', 'subdivision', 'phase', 'lot_area', 'reservation_fee', 'reservation_date', 'expiry_date', 'status', 'converted_lot_id', 'notes'])]
class Reservation extends Model
{
    use HasUuids;

        protected function casts(): array
    {
        return [
            'lot_area' => 'float',
            'reservation_fee' => 'float',
            'reservation_date' => 'date',
            'expiry_date' => 'date',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function convertedLot(): BelongsTo
    {
        return $this->belongsTo(Lot::class, 'converted_lot_id');
    }
}
