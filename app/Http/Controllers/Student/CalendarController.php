<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\CalendarService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function __construct(private readonly CalendarService $calendarService) {}

    public function index(Request $request): JsonResponse
    {
        $month  = (int) $request->query('month', now()->month);
        $year   = (int) $request->query('year',  now()->year);
        $userId = (string) $request->user()->_id;

        $events = $this->calendarService->getEvents($userId, $month, $year);

        return response()->json(['events' => $events]);
    }
}
