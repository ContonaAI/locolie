<?php

namespace App\Http\Controllers;

use App\Services\ReportingService;
use Illuminate\Http\Request;

/** Team-facing platform reporting across the whole marketplace. */
class ReportsController extends Controller
{
    public function platform(Request $request, ReportingService $reporting)
    {
        $days = (int) $request->integer('days', 30);
        $days = in_array($days, [7, 14, 30, 90], true) ? $days : 30;

        return view('portal.reports', [
            'report' => $reporting->platform($days),
            'days' => $days,
        ]);
    }
}
