export const PAYMENT_METHODS = [
    { value: 'cash', label: 'Cash' },
    { value: 'bank_transfer', label: 'Bank Transfer' },
    { value: 'gcash', label: 'GCash' },
    { value: 'check', label: 'Check' },
] as const;

export const methodLabel = (method: string | null) =>
    PAYMENT_METHODS.find((m) => m.value === method)?.label ??
    (method ? method.replace(/_/g, ' ') : '—');

export const PAYMENT_STATUS: Record<string, { label: string; classes: string; dot: string }> = {
    posted: { label: 'Posted', classes: 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:ring-emerald-800', dot: 'bg-emerald-500' },
    voided: { label: 'Voided', classes: 'bg-red-50 text-red-700 ring-1 ring-red-200 dark:bg-red-900/30 dark:text-red-400 dark:ring-red-800', dot: 'bg-red-500' },
};

// Local date (YYYY-MM-DD). toISOString() uses UTC and shows yesterday early in the morning in PH.
export const todayISO = () => new Date().toLocaleDateString('en-CA');
