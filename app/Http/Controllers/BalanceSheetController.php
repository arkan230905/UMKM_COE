<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\JournalLine;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BalanceSheetController extends Controller
{
    public function index(Request $request)
    {
        $periodInput = $request->input('period', now()->format('Y-m'));

        try {
            $periodDate = Carbon::createFromFormat('Y-m', $periodInput)->startOfMonth();
        } catch (\Exception $exception) {
            $periodDate = now()->startOfMonth();
            $periodInput = $periodDate->format('Y-m');
        }

        $endDate = $periodDate->copy()->endOfMonth();
        $endDateString = $endDate->toDateString();

        $lineAggregates = JournalLine::query()
            ->select(
                'journal_lines.account_id',
                DB::raw('SUM(journal_lines.debit) as total_debit'),
                DB::raw('SUM(journal_lines.credit) as total_credit')
            )
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->whereDate('journal_entries.tanggal', '<=', $endDateString)
            ->groupBy('journal_lines.account_id');

        $accountsQuery = Account::query()
            ->leftJoin('coas', 'coas.kode_akun', '=', 'accounts.code')
            ->leftJoinSub($lineAggregates, 'ledger', function ($join) {
                $join->on('ledger.account_id', '=', 'accounts.id');
            });

        if (Schema::hasColumn('coas', 'is_active')) {
            $accountsQuery->where(function ($query) {
                $query->whereNull('coas.is_active')->orWhere('coas.is_active', true);
            });
        }

        if (Schema::hasColumn('coas', 'is_akun_header')) {
            $accountsQuery->where(function ($query) {
                $query->whereNull('coas.is_akun_header')->orWhere('coas.is_akun_header', false);
            });
        }

        $accounts = $accountsQuery
            ->select(
                'accounts.id',
                'accounts.code',
                'accounts.name',
                'accounts.type',
                DB::raw('LOWER(COALESCE(coas.tipe_akun, accounts.type)) as coa_type'),
                DB::raw('COALESCE(coas.kategori_akun, "") as kategori_akun'),
                DB::raw('LOWER(COALESCE(coas.saldo_normal, "")) as saldo_normal'),
                DB::raw('COALESCE(coas.saldo_awal, 0) as saldo_awal'),
                DB::raw('COALESCE(ledger.total_debit, 0) as total_debit'),
                DB::raw('COALESCE(ledger.total_credit, 0) as total_credit')
            )
            ->get();

        $assetGroups = [];
        $liabilityGroups = [];
        $equityGroups = [];

        foreach ($accounts as $account) {
            $type = strtolower($account->type ?? $account->coa_type ?? '');
            $normal = $this->determineNormalBalance($account->saldo_normal, $type);

            $balance = $normal === 'debit'
                ? (float) $account->saldo_awal + (float) $account->total_debit - (float) $account->total_credit
                : (float) $account->saldo_awal - (float) $account->total_debit + (float) $account->total_credit;

            if (abs($balance) < 0.0005) {
                continue;
            }

            $item = [
                'code' => $account->code,
                'name' => $account->name,
                'amount' => $balance,
                'category' => $account->kategori_akun,
            ];

            if ($type === 'asset') {
                $groupKey = $this->mapAssetCategory($account->kategori_akun);
                $this->pushGroupItem($assetGroups, $groupKey, $item);
            } elseif ($type === 'liability') {
                $groupKey = $this->mapLiabilityCategory($account->kategori_akun);
                $this->pushGroupItem($liabilityGroups, $groupKey, $item);
            } elseif ($type === 'equity') {
                $groupKey = $this->mapEquityCategory($account->kategori_akun);
                $this->pushGroupItem($equityGroups, $groupKey, $item);
            }
        }

        $netProfit = $this->calculateNetProfit($endDateString);
        if (abs($netProfit) >= 0.0005) {
            $equityGroupKey = 'Modal & Ekuitas';
            if (!isset($equityGroups[$equityGroupKey])) {
                $equityGroups[$equityGroupKey] = [
                    'label' => $equityGroupKey,
                    'items' => [],
                    'adjustment' => 0.0,
                    'meta' => [],
                ];
            }

            $equityGroups[$equityGroupKey]['adjustment'] = ($equityGroups[$equityGroupKey]['adjustment'] ?? 0) + $netProfit;
            $equityGroups[$equityGroupKey]['meta']['net_profit'] = $netProfit;
        }

        $assetGroups = $this->finalizeGroups($assetGroups, ['Aktiva Lancar', 'Aktiva Tetap']);
        $liabilityGroups = $this->finalizeGroups($liabilityGroups, ['Kewajiban']);
        $equityGroups = $this->finalizeGroups($equityGroups, ['Modal & Ekuitas']);

        $totalAssets = $this->sumTotal($assetGroups);
        $totalLiabilities = $this->sumTotal($liabilityGroups);
        $totalEquity = $this->sumTotal($equityGroups);
        $totalLiabilitiesEquity = $totalLiabilities + $totalEquity;

        return view('akuntansi.neraca', [
            'period' => $periodInput,
            'periodLabel' => $periodDate->translatedFormat('F Y'),
            'companyName' => config('app.name', 'UMKM COE'),
            'assetGroups' => $assetGroups,
            'liabilityGroups' => $liabilityGroups,
            'equityGroups' => $equityGroups,
            'totalAssets' => $totalAssets,
            'totalLiabilities' => $totalLiabilities,
            'totalEquity' => $totalEquity,
            'totalLiabilitiesEquity' => $totalLiabilitiesEquity,
        ]);
    }

    private function determineNormalBalance(?string $normal, string $type): string
    {
        $normalized = strtolower((string) $normal);
        if (in_array($normalized, ['debit', 'credit'], true)) {
            return $normalized;
        }

        return in_array($type, ['asset', 'expense'], true) ? 'debit' : 'credit';
    }

    private function mapAssetCategory(?string $kategori): string
    {
        $value = strtolower((string) $kategori);
        if (str_contains($value, 'tetap') || str_contains($value, 'fixed')) {
            return 'Aktiva Tetap';
        }

        return 'Aktiva Lancar';
    }

    private function mapLiabilityCategory(?string $kategori): string
    {
        return 'Kewajiban';
    }

    private function mapEquityCategory(?string $kategori): string
    {
        return 'Modal & Ekuitas';
    }

    private function finalizeGroups(array $groups, array $preferredOrder): array
    {
        foreach ($groups as $label => &$group) {
            $items = collect($group['items'] ?? [])
                ->sortBy(fn ($item) => $item['code'] ?? '')
                ->values()
                ->all();

            $group['items'] = $items;

            $itemsTotal = array_sum(array_map(fn ($item) => (float) $item['amount'], $items));
            $adjustment = (float) ($group['adjustment'] ?? 0);
            $group['subtotal'] = $itemsTotal + $adjustment;
        }
        unset($group);

        $ordered = [];
        foreach ($preferredOrder as $label) {
            if (isset($groups[$label])) {
                $ordered[] = $groups[$label];
                unset($groups[$label]);
            }
        }

        foreach ($groups as $group) {
            $ordered[] = $group;
        }

        return $ordered;
    }

    private function pushGroupItem(array &$groups, string $groupKey, array $item): void
    {
        if (!isset($groups[$groupKey])) {
            $groups[$groupKey] = [
                'label' => $groupKey,
                'items' => [],
                'adjustment' => 0.0,
                'meta' => [],
            ];
        }

        $groups[$groupKey]['items'][] = $item;
    }

    private function sumTotal(array $groups): float
    {
        return array_sum(array_map(function ($group) {
            return (float) ($group['subtotal'] ?? 0);
        }, $groups));
    }

    private function calculateNetProfit(string $endDate): float
    {
        $lines = JournalLine::query()
            ->join('accounts', 'accounts.id', '=', 'journal_lines.account_id')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->whereIn('accounts.type', ['revenue', 'expense'])
            ->whereDate('journal_entries.tanggal', '<=', $endDate)
            ->select(
                'accounts.type',
                DB::raw('SUM(journal_lines.debit) as total_debit'),
                DB::raw('SUM(journal_lines.credit) as total_credit')
            )
            ->groupBy('accounts.type')
            ->get();

        $revenue = 0.0;
        $expense = 0.0;

        foreach ($lines as $line) {
            if ($line->type === 'revenue') {
                $revenue += (float) $line->total_credit - (float) $line->total_debit;
            } else {
                $expense += (float) $line->total_debit - (float) $line->total_credit;
            }
        }

        return $revenue - $expense;
    }
}
