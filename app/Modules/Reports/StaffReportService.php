<?php

namespace App\Modules\Reports;

use App\Models\User;
use App\Modules\Support\SupportTicket;
use App\Modules\Transactions\TransactionApproval;
use Carbon\Carbon;

class StaffReportService
{
    public function employeePerformance($from, $to)
    {
        $from = Carbon::parse($from);
        $to   = Carbon::parse($to);

        $employees = User::whereIn('role', ['teller'])->get();

        $reports = [];

        foreach ($employees as $employee) {
            $reports[] = [
                'employee' => $employee->name,
                'role'     => $employee->role,

                // عدد التذاكر التي رد عليها
                'support_replies' => $employee->ticketReplies()
                    ->whereBetween('created_at', [$from, $to])->count(),

                // عدد الموافقات التي نفذها
                'approvals' => $employee->transactionApprovals()
                    ->whereBetween('action_taken_at', [$from, $to])->count(),

                // عدد العمليات التي نفذها
                'handled_transactions' => $employee->transactions()
                    ->whereBetween('created_at', [$from, $to])->count(),
            ];
        }

        return $reports;
    }
}
