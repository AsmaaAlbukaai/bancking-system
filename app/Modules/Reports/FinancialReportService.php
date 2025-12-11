<?php

namespace App\Modules\Reports;

use App\Modules\Transaction\Transaction;
use Carbon\Carbon;

class FinancialReportService
{
    public function summary($from, $to)
    {
        // تنظيف وتحويل التاريخ
        $from = Carbon::parse($from)->startOfDay();
        $to   = Carbon::parse($to)->endOfDay();

        return [

            // إجمالي الإيداعات
            'total_deposits' => Transaction::where('type', 'deposit')
                ->whereBetween('created_at', [$from, $to])
                ->sum('amount'),

            // إجمالي السحوبات
            'total_withdrawals' => Transaction::where('type', 'withdrawal')
                ->whereBetween('created_at', [$from, $to])
                ->sum('amount'),

            // إجمالي التحويلات
            'total_transfers' => Transaction::where('type', 'transfer')
                ->whereBetween('created_at', [$from, $to])
                ->sum('amount'),

            // إجمالي المدفوعات
            'total_payments' => Transaction::where('type', 'payment')
                ->whereBetween('created_at', [$from, $to])
                ->sum('amount'),

            // إجمالي الفوائد المكتسبة
            'total_interest' => Transaction::where('type', 'interest')
                ->whereBetween('created_at', [$from, $to])
                ->sum('amount'),

            // إجمالي الرسوم (من كامل النظام)
            'total_fees_collected' => Transaction::whereBetween('created_at', [$from, $to])
                ->sum('fee'),

            // إجمالي الضرائب
            'total_tax_collected' => Transaction::whereBetween('created_at', [$from, $to])
                ->sum('tax'),

            // إجمالي المبالغ المخصومة كرسوم (type=fee)
            'fee_transactions_total' => Transaction::where('type', 'fee')
                ->whereBetween('created_at', [$from, $to])
                ->sum('amount'),

            // إجمالي المرتجعات
            'total_refunds' => Transaction::where('type', 'refund')
                ->whereBetween('created_at', [$from, $to])
                ->sum('amount'),

            // صافي الربح (الرسوم + الضرائب)
            'net_profit' =>
                Transaction::whereBetween('created_at', [$from, $to])->sum('fee') +
                Transaction::whereBetween('created_at', [$from, $to])->sum('tax'),
        ];
    }
}
