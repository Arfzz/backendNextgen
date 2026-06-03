<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Services\CalendarService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function __construct(private readonly CalendarService $calendarService) {}

    public function index(Request $request): JsonResponse
    {
        $month    = (int) $request->query('month', now()->month);
        $year     = (int) $request->query('year',  now()->year);
        $mentorId = (string) $request->user()->getAuthIdentifier();

        $events = $this->calendarService->getMentorEvents($mentorId, $month, $year);

        return response()->json($events);
    }
}
