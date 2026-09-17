<?php

namespace App\Support;

class Labels
{
    public static function paymentMethod(?string $key): string
    {
        return match ($key) {
            'cash' => __('Cash'),
            'qris' => __('QRIS'),
            'transfer' => __('Bank transfer'),
            'credit' => __('Credit'),
            default => __('Unknown'),
        };
    }

    public static function paymentStatus(?string $key): string
    {
        return match ($key) {
            'paid' => __('Paid'),
            'partial' => __('Partial'),
            'unpaid' => __('Unpaid'),
            'pending' => __('Pending'),
            default => __('Unknown'),
        };
    }

    public static function userStatus(?string $key): string
    {
        return match ($key) {
            'active' => __('Active'),
            'inactive' => __('Inactive'),
            default => __('Unknown'),
        };
    }
}
