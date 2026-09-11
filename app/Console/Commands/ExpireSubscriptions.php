<?php

namespace App\Console\Commands;

use App\Services\SubscriptionLifecycleService;
use Illuminate\Console\Command;

class ExpireSubscriptions extends Command
{
    protected $signature = 'subscription:expire';

    protected $description = 'Menurunkan user premium yang masa berlangganannya telah berakhir kembali ke Free Tier.';

    public function handle(SubscriptionLifecycleService $lifecycle): int
    {
        $count = $lifecycle->expireOverdueSubscriptions();

        $this->info("Ditemukan {$count} langganan yang kedaluwarsa dan telah dikembalikan ke Free Tier.");

        return self::SUCCESS;
    }
}