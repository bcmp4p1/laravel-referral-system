<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthService
{
    public function register(array $data): User
    {
        $referrer = null;

        if (!empty($data['referral_code'])) {
            $referrer = User::where('referral_code', $data['referral_code'])->first();
        }

        $user = User::create([
            'name'         => $data['name'],
            'email'        => $data['email'],
            'password'     => Hash::make($data['password']),
            'referral_code'=> Str::random(10),
            'referred_by'   => $referrer?->id,
            'balance'       => 0,
        ]);

        $this->distributeReferralBonuses($user, 100);

        return $user;
    }

    protected function distributeReferralBonuses(User $newUser, float $amount): void
    {
        $percentages = [0.10, 0.01, 0.001];
        $currentReferrer = $newUser->referred_by;

        foreach ($percentages as $level => $percent) {
            if (!$currentReferrer) break;

            $referrer = User::find($currentReferrer);
            if (!$referrer) break;

            $bonus = round($amount * $percent, 2);
            $referrer->increment('balance', $bonus);

            $currentReferrer = $referrer->referred_by;
        }
    }
}
