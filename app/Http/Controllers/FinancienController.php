<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use App\Models\ProjectInvoice; // pas aan als model anders heet
use App\Models\ProjectQuote;   // pas aan als model anders heet

class FinancienController extends Controller
{
    public function index(Request $request)
    {
        $user   = $request->user();
        $period = (string) $request->query('period', 'this_month');

        // Klantfilter doen we later echt
        $customer = trim((string) $request->query('customer', ''));

        [$from, $to] = $this->resolvePeriod($request, $period);

        // -----------------------------
        // Base queries (geen company_id!)
        // -----------------------------
        $quotesQ   = ProjectQuote::query();
        $invoicesQ = ProjectInvoice::query();

        if ($from) {
            $quotesQ->whereDate('quote_date', '>=', $from->toDateString());
            $invoicesQ->whereDate('invoice_date', '>=', $from->toDateString());
        }
        if ($to) {
            $quotesQ->whereDate('quote_date', '<=', $to->toDateString());
            $invoicesQ->whereDate('invoice_date', '<=', $to->toDateString());
        }

        // -----------------------------
        // Offertes analytics
        // -----------------------------
        $totalQuotes     = (clone $quotesQ)->count();
        $signedQuotes    = (clone $quotesQ)->where('status', 'accepted')->count();
        $cancelledQuotes = (clone $quotesQ)->where('status', 'cancelled')->count();
        $activeQuotes    = (clone $quotesQ)->whereIn('status', ['draft', 'sent'])->count();

        $quoteSignedRate = $totalQuotes > 0
            ? round(($signedQuotes / $totalQuotes) * 100, 1)
            : 0.0;

        $quoteStatusCounts = (clone $quotesQ)
            ->selectRaw('LOWER(COALESCE(status,"draft")) as s, COUNT(*) as c')
            ->groupBy('s')
            ->pluck('c', 's')
            ->toArray();

        $quoteStatusAmounts = (clone $quotesQ)
            ->selectRaw('LOWER(COALESCE(status,"draft")) as s, COALESCE(SUM(total_cents),0) as a')
            ->groupBy('s')
            ->pluck('a', 's')
            ->map(fn ($v) => (int) $v)
            ->toArray();

        // -----------------------------
        // Facturen analytics
        // -----------------------------
        $totalIncomeCents = (int) (clone $invoicesQ)->where('status', 'paid')->sum('total_cents');

        $openInvoicesQ      = (clone $invoicesQ)->whereNotIn('status', ['paid', 'cancelled']);
        $openInvoicesCount  = (int) $openInvoicesQ->count();
        $openInvoicesCents  = (int) $openInvoicesQ->sum('total_cents');

        $invoiceStatusCounts = (clone $invoicesQ)
            ->selectRaw('LOWER(COALESCE(status,"draft")) as s, COUNT(*) as c')
            ->groupBy('s')
            ->pluck('c', 's')
            ->toArray();

        $invoiceStatusAmounts = (clone $invoicesQ)
            ->selectRaw('LOWER(COALESCE(status,"draft")) as s, COALESCE(SUM(total_cents),0) as a')
            ->groupBy('s')
            ->pluck('a', 's')
            ->map(fn ($v) => (int) $v)
            ->toArray();

        // Gem. betalingstermijn
        $avgPaymentDays = null;

        $paidAtColumn = null;
        if (Schema::hasColumn('project_invoices', 'paid_at')) {
            $paidAtColumn = 'paid_at';
        } elseif (Schema::hasColumn('project_invoices', 'paid_date')) {
            $paidAtColumn = 'paid_date';
        }

        $selectCols = ['invoice_date', 'due_date'];
        if ($paidAtColumn) $selectCols[] = $paidAtColumn;

        $paidInvoices = (clone $invoicesQ)
            ->where('status', 'paid')
            ->whereNotNull('invoice_date')
            ->get($selectCols);

        $days = [];
        foreach ($paidInvoices as $inv) {
            $invoiceDate = $inv->invoice_date ? Carbon::parse($inv->invoice_date) : null;
            if (!$invoiceDate) continue;

            if ($paidAtColumn && !empty($inv->{$paidAtColumn})) {
                $paidDate = Carbon::parse($inv->{$paidAtColumn});
                $days[] = $invoiceDate->diffInDays($paidDate);
                continue;
            }

            if (!empty($inv->due_date)) {
                $dueDate = Carbon::parse($inv->due_date);
                $days[] = $invoiceDate->diffInDays($dueDate);
            }
        }

        if (count($days) > 0) {
            $avgPaymentDays = (int) round(array_sum($days) / count($days));
        }

        // Achterstallig buckets
        $overdueBuckets = [
            '0-7'   => ['label' => '0–7 dagen',   'count' => 0, 'cents' => 0],
            '8-14'  => ['label' => '8–14 dagen',  'count' => 0, 'cents' => 0],
            '15-30' => ['label' => '15–30 dagen', 'count' => 0, 'cents' => 0],
            '31+'   => ['label' => '31+ dagen',   'count' => 0, 'cents' => 0],
        ];

        $today = Carbon::today();
        $overdues = (clone $openInvoicesQ)
            ->whereNotNull('due_date')
            ->get(['due_date', 'total_cents']);

        foreach ($overdues as $inv) {
            $due = Carbon::parse($inv->due_date);
            if ($due->gte($today)) continue;

            $d = $due->diffInDays($today);
            $c = (int) ($inv->total_cents ?? 0);

            if ($d <= 7) {
                $overdueBuckets['0-7']['count']++;
                $overdueBuckets['0-7']['cents'] += $c;
            } elseif ($d <= 14) {
                $overdueBuckets['8-14']['count']++;
                $overdueBuckets['8-14']['cents'] += $c;
            } elseif ($d <= 30) {
                $overdueBuckets['15-30']['count']++;
                $overdueBuckets['15-30']['cents'] += $c;
            } else {
                $overdueBuckets['31+']['count']++;
                $overdueBuckets['31+']['cents'] += $c;
            }
        }

        // -----------------------------
        // Chart: Inkomstenoverzicht (per maand huidig jaar)
        // -----------------------------
        $year = (int) now()->year;

        $monthsNl = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Aug','Sep','Okt','Nov','Dec'];

        $invoiceByMonth = ProjectInvoice::query()
            ->selectRaw('MONTH(invoice_date) as m, COALESCE(SUM(total_cents),0) as s')
            ->whereYear('invoice_date', $year)
            ->whereNotIn('status', ['cancelled'])
            ->groupBy('m')
            ->pluck('s', 'm')
            ->map(fn ($v) => (int) $v)
            ->toArray();

        $offerByMonth = ProjectQuote::query()
            ->selectRaw('MONTH(quote_date) as m, COALESCE(SUM(total_cents),0) as s')
            ->whereYear('quote_date', $year)
            ->whereNotIn('status', ['cancelled'])
            ->groupBy('m')
            ->pluck('s', 'm')
            ->map(fn ($v) => (int) $v)
            ->toArray();

        $chartInvoices = [];
        $chartOffers   = [];

        for ($m = 1; $m <= 12; $m++) {
            $chartInvoices[] = round(((int)($invoiceByMonth[$m] ?? 0)) / 100, 2);
            $chartOffers[]   = round(((int)($offerByMonth[$m] ?? 0)) / 100, 2);
        }

        return view('hub.financien.index', [
            'user' => $user,

            'filters' => [
                'period'   => $period,
                'customer' => $customer,
                'start'    => $request->query('start'),
                'end'      => $request->query('end'),
            ],

            'kpis' => [
                'total_income_cents'   => $totalIncomeCents,
                'open_invoices_count'  => $openInvoicesCount,
                'open_invoices_cents'  => $openInvoicesCents,
                'avg_payment_days'     => $avgPaymentDays,
                'active_quotes_count'  => $activeQuotes,
            ],

            'breakdown' => [
                'quote_status_counts'    => $quoteStatusCounts,
                'quote_status_amounts'   => $quoteStatusAmounts,
                'invoice_status_counts'  => $invoiceStatusCounts,
                'invoice_status_amounts' => $invoiceStatusAmounts,
                'overdue_buckets'        => $overdueBuckets,
            ],

            // ✅ BELANGRIJK: key heet offers (niet quotes), zodat je Blade chart['offers'] niet crasht
            'chart' => [
                'labels'   => $monthsNl,
                'invoices' => $chartInvoices,
                'offers'   => $chartOffers,
            ],
        ]);
    }

    public function facturen(Request $request)
    {
        $user = $request->user();

        $invoices = ProjectInvoice::query()
            ->orderByDesc('invoice_date')
            ->orderByDesc('id')
            ->get();

        return view('hub.financien.facturen', [
            'user' => $user,
            'invoices' => $invoices,
        ]);
    }

    public function offertes(Request $request)
    {
        $user = $request->user();

        $quotes = ProjectQuote::query()
            ->orderByDesc('quote_date')
            ->orderByDesc('id')
            ->get();

        return view('hub.financien.offertes', [
            'user' => $user,
            'quotes' => $quotes,
        ]);
    }

    private function resolvePeriod(Request $request, string $period): array
    {
        $now = now();

        return match ($period) {
            'this_month' => [$now->copy()->startOfMonth()->startOfDay(), $now->copy()->endOfMonth()->endOfDay()],
            'last_month' => [
                $now->copy()->subMonthNoOverflow()->startOfMonth()->startOfDay(),
                $now->copy()->subMonthNoOverflow()->endOfMonth()->endOfDay()
            ],
            'last_30'    => [$now->copy()->subDays(29)->startOfDay(), $now->copy()->endOfDay()],
            'this_year'  => [$now->copy()->startOfYear()->startOfDay(), $now->copy()->endOfYear()->endOfDay()],
            'all'        => [null, null],
            'custom'     => [
                $request->query('start') ? Carbon::parse($request->query('start'))->startOfDay() : null,
                $request->query('end')   ? Carbon::parse($request->query('end'))->endOfDay() : null,
            ],
            default      => [$now->copy()->startOfMonth()->startOfDay(), $now->copy()->endOfMonth()->endOfDay()],
        };
    }
}
