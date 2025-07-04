<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class FundingController extends Controller
{
    protected ReferralService $referralService;

    public function __construct(ReferralService $referralService)
    {
        $this->referralService = $referralService;
    }

    public function fund(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        $user = $request->user();
        $user->increment('balance', $data['amount']);

        $this->referralService->distribute($user, $data['amount']);

        return response()->json([
            'message' => 'Funded successfully',
            'balance' => $user->balance,
        ]);
    }
}
