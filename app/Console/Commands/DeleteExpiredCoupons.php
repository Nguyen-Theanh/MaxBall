<?php

namespace App\Console\Commands;

use App\Models\Coupon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('coupons:delete-expired')]
#[Description('Tự động xóa các voucher đã hết hạn')]
class DeleteExpiredCoupons extends Command
{
    public function handle(): int
    {
        $deletedCount = Coupon::query()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->delete();

        $this->info("Đã xóa {$deletedCount} voucher hết hạn.");

        return self::SUCCESS;
    }
}
