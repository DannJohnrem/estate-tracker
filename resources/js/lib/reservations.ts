export type ReservationStatus = 'pending' | 'confirmed' | 'expired' | 'cancelled';

export const RESERVATION_STATUS: Record<string, { label: string; classes: string; dot: string }> = {
    pending:   { label: 'Pending',   classes: 'bg-amber-50 text-amber-700 ring-1 ring-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:ring-amber-800', dot: 'bg-amber-500' },
    confirmed: { label: 'Confirmed', classes: 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:ring-emerald-800', dot: 'bg-emerald-500' },
    expired:   { label: 'Expired',   classes: 'bg-red-50 text-red-700 ring-1 ring-red-200 dark:bg-red-900/30 dark:text-red-400 dark:ring-red-800', dot: 'bg-red-500' },
    cancelled: { label: 'Cancelled', classes: 'bg-gray-100 text-gray-500 ring-1 ring-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700', dot: 'bg-gray-400' },
};

export const RESERVATION_STATUS_OPTIONS = [
    { value: 'pending', label: 'Pending' },
    { value: 'confirmed', label: 'Confirmed' },
    { value: 'expired', label: 'Expired' },
    { value: 'cancelled', label: 'Cancelled' },
];

export const formatPeso = (amount: number | string | null | undefined) =>
    amount === null || amount === undefined || amount === ''
        ? '—'
        : '₱ ' + Number(amount).toLocaleString('en-PH', { minimumFractionDigits: 2 });

export const formatDate = (date: string | null | undefined) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' });
};

// Laravel serializes `date` casts as ISO datetimes; <input type="date"> needs YYYY-MM-DD
export const dateInputValue = (date: string | null | undefined) => (date ? date.slice(0, 10) : '');

export const personName = (p: { first_name: string; middle_name: string | null; last_name: string } | null | undefined) =>
    p ? [p.first_name, p.middle_name, p.last_name].filter(Boolean).join(' ') : '—';

export const lotLabel = (r: { block_number: string | null; lot_number: string }) =>
    r.block_number ? `Blk ${r.block_number} Lot ${r.lot_number}` : `Lot ${r.lot_number}`;

// A pending reservation whose hold period has already passed
export const isPastExpiry = (r: { status: string; expiry_date: string | null }) =>
    r.status === 'pending' && !!r.expiry_date && new Date(r.expiry_date) < new Date(new Date().toDateString());
