<?php

namespace App\Modules\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function financialReport(Request $request)
    {
        $this->authorizeRole(['admin']);

        $service = new FinancialReportService();
        return response()->json(
            $service->summary($request->from, $request->to)
        );
    }

    public function staffReport(Request $request)
    {
        $this->authorizeRole(['manager']);

        $service = new StaffReportService();
        return response()->json(
            $service->employeePerformance($request->from, $request->to)
        );
    }

    private function authorizeRole($roles)
    {
        if (!in_array(auth()->user()->role, $roles)) {
            abort(403, 'Unauthorized');
        }
    }
}
