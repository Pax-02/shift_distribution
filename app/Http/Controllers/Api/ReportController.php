<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Reports\HoursReportService;
use App\Services\Reports\ShiftsReportService;
use App\Services\Reports\WorkersReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function hours(Request $request, HoursReportService $service): JsonResponse
    {
        $this->authorize('viewReports');

        return response()->json(['data' => $service->forWeek($request->string('week')->value() ?: null)]);
    }

    public function workers(WorkersReportService $service): JsonResponse
    {
        $this->authorize('viewReports');

        return response()->json(['data' => $service->summary()]);
    }

    public function shifts(Request $request, ShiftsReportService $service): JsonResponse
    {
        $this->authorize('viewReports');

        return response()->json(['data' => $service->summary(
            $request->string('from')->value() ?: null,
            $request->string('to')->value() ?: null,
        )]);
    }
}
