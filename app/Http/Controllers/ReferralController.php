<?php

namespace App\Http\Controllers;

use App\Services\ReferralService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    protected ReferralService $referralService;

    public function __construct(ReferralService $referralService)
    {
        $this->referralService = $referralService;
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $referralData = $this->referralService->getReferralStats($user);

        return response()->json($referralData);
    }
}
