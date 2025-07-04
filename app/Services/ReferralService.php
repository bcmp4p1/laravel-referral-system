<?php

namespace App\Services;

use App\Models\User;
use App\Models\ReferralTransaction;

class ReferralService
{
    public function distribute(User $sourceUser, float $amount): void
    {
        $percentages = [0.10, 0.01, 0.001];
        $currentReferrer = $sourceUser->referred_by;

        foreach ($percentages as $percent) {
            if (!$currentReferrer) break;

            $referrer = User::find($currentReferrer);
            if (!$referrer) break;

            $bonus = round($amount * $percent, 2);
            $referrer->increment('balance', $bonus);

            ReferralTransaction::create([
                'referrer_id' => $referrer->id,
                'referred_id' => $sourceUser->id,
                'amount' => $bonus,
            ]);

            $currentReferrer = $referrer->referred_by;
        }
    }

    public function getReferralStats(User $user): array
    {
        $level1 = $user->referrals ?? collect();
        $level2 = $level1->flatMap(fn($u) => $u->referrals ?? collect());
        $level3 = $level2->flatMap(fn($u) => $u->referrals ?? collect());

        $collectBonus = function ($referrals) use ($user) {
            $bonus = 0;
            foreach ($referrals as $ref) {
                $bonus += \DB::table('referral_transactions')
                    ->where('referrer_id', $user->id)
                    ->where('referred_id', $ref->id)
                    ->sum('amount');
            }

            return [
                'count' => $referrals->count(),
                'bonus_earned' => round($bonus, 2),
                'users' => $referrals->map(fn($u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'balance' => $u->balance,
                ]),
            ];
        };

        $l1 = $collectBonus($level1);
        $l2 = $collectBonus($level2);
        $l3 = $collectBonus($level3);

        return [
            'level_1' => $l1,
            'level_2' => $l2,
            'level_3' => $l3,
            'total_earned' => round($l1['bonus_earned'] + $l2['bonus_earned'] + $l3['bonus_earned'], 2),
        ];
    }
}
