<?php

namespace Database\Seeders;

use App\Modules\Finance\Models\FinCoaAccount;
use App\Modules\Finance\Models\FinFsli;
use Illuminate\Database\Seeder;

class FinCoaHierarchySeeder extends Seeder
{
    public function run(): void
    {
        $defaultFsli = FinFsli::first();
        if (! $defaultFsli) {
            return;
        }

        $headers = [
            ['code' => '1000', 'name' => 'ASET (AKTIVA)', 'type' => 'asset', 'balance' => 'debit', 'fsli' => 'BS_ASSET'],
            ['code' => '1100', 'name' => 'Aset Lancar (Current Assets)', 'type' => 'asset', 'balance' => 'debit', 'fsli' => 'BS_ASSET_CURR', 'parent' => '1000'],
            ['code' => '1500', 'name' => 'Aset Tetap & Non-Lancar', 'type' => 'asset', 'balance' => 'debit', 'fsli' => 'BS_ASSET_FIXED', 'parent' => '1000'],

            ['code' => '2000', 'name' => 'KEWAJIBAN (LIABILITAS)', 'type' => 'liability', 'balance' => 'credit', 'fsli' => 'BS_LIAB'],
            ['code' => '2100', 'name' => 'Kewajiban Jangka Pendek', 'type' => 'liability', 'balance' => 'credit', 'fsli' => 'BS_LIAB_CURR', 'parent' => '2000'],

            ['code' => '3000', 'name' => 'EKUITAS (MODAL)', 'type' => 'equity', 'balance' => 'credit', 'fsli' => 'BS_EQUITY'],
            ['code' => '3100', 'name' => 'Modal Saham & Laba Ditahan', 'type' => 'equity', 'balance' => 'credit', 'fsli' => 'BS_EQUITY', 'parent' => '3000'],

            ['code' => '4000', 'name' => 'PENDAPATAN USAHA (REVENUE)', 'type' => 'income', 'balance' => 'credit', 'fsli' => 'REV_SALES'],
            ['code' => '5000', 'name' => 'BEBAN POKOK PENJUALAN (HPP)', 'type' => 'expense', 'balance' => 'debit', 'fsli' => 'COGS'],
            ['code' => '6000', 'name' => 'BEBAN OPERASIONAL (OPEX)', 'type' => 'expense', 'balance' => 'debit', 'fsli' => 'OPEX'],
        ];

        foreach ($headers as $item) {
            $fsli = FinFsli::where('code', $item['fsli'])->first() ?? $defaultFsli;
            $parent = isset($item['parent']) ? FinCoaAccount::where('account_code', $item['parent'])->first() : null;

            FinCoaAccount::updateOrCreate(
                ['account_code' => $item['code']],
                [
                    'account_name' => $item['name'],
                    'account_type' => $item['type'],
                    'normal_balance' => $item['balance'],
                    'is_header' => true,
                    'parent_id' => $parent?->id,
                    'fsli_id' => $fsli->id,
                    'is_active' => true,
                    'opening_balance' => 0,
                ]
            );
        }

        // Map child accounts to their respective headers
        $parent1100 = FinCoaAccount::where('account_code', '1100')->first();
        $parent1500 = FinCoaAccount::where('account_code', '1500')->first();
        $parent2100 = FinCoaAccount::where('account_code', '2100')->first();
        $parent3100 = FinCoaAccount::where('account_code', '3100')->first();
        $parent4000 = FinCoaAccount::where('account_code', '4000')->first();
        $parent5000 = FinCoaAccount::where('account_code', '5000')->first();
        $parent6000 = FinCoaAccount::where('account_code', '6000')->first();

        FinCoaAccount::whereIn('account_code', ['1101', '1102', '1201', '1301', '1302', '1401'])->update(['parent_id' => $parent1100?->id]);
        FinCoaAccount::whereIn('account_code', ['1501', '1599'])->update(['parent_id' => $parent1500?->id]);
        FinCoaAccount::whereIn('account_code', ['2101', '2102'])->update(['parent_id' => $parent2100?->id]);
        FinCoaAccount::whereIn('account_code', ['3101', '3201'])->update(['parent_id' => $parent3100?->id]);
        FinCoaAccount::where('account_code', 'like', '4%')->where('is_header', false)->update(['parent_id' => $parent4000?->id]);
        FinCoaAccount::where('account_code', 'like', '5%')->where('is_header', false)->update(['parent_id' => $parent5000?->id]);
        FinCoaAccount::where('account_code', 'like', '6%')->where('is_header', false)->update(['parent_id' => $parent6000?->id]);
    }
}
