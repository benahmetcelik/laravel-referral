<?php

/*
 * This file is part of questocat/laravel-referral package.
 *
 * (c) questocat <zhengchaopu@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Questocat\Referral\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

trait UserReferral
{
    public function getReferralLink()
    {
        if ($this->affiliate_id == null) {
            $this->affiliate_id = self::generateReferral();
            $this->save();
        }
        return url('/') . '/?ref=' . $this->affiliate_id;
    }

    public function getAllReferral()
    {
        $new_model = new static();
        return $new_model->where('referred_by', $this->affiliate_id);
    }

    public function getAffilateUser($column)
    {
            $user =app(config('referral.user_model'))::whereAffiliateId($this->referred_by)->first();
            if ($user){
                return $user->$column;
            }
           return null;
    }



    public static function scopeReferralExists(Builder $query, $referral)
    {
        return $query->whereAffiliateId($referral)->exists();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if ($referredBy = Cookie::get('referral')) {
                $model->referred_by = $referredBy;
            }

            $model->affiliate_id = self::generateReferral();
        });


        static::updating(function ($model) {
            if ($model->affiliate_id == null) {

                $model->affiliate_id = self::generateReferral();
            }
        });

    }

    protected static function generateReferral()
    {
        $length = config('referral.referral_length', 5);

        do {
            $referral = Str::random($length);
        } while (static::referralExists($referral));

        return $referral;
    }
}
