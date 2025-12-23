<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Membership;
use Illuminate\Console\Command;

class RefreshMembershipStatus extends Command
{
    protected $signature = 'memberships:refresh';
    protected $description = 'Refresh expired membership status';

    public function handle(): int
    {
        // Update user membership status
        $updatedUsers = User::where('is_member', true)
            ->where('member_until', '<', now())
            ->update(['is_member' => false]);

        // Update membership records
        $updatedMemberships = Membership::where('status', 'active')
            ->where('expires_at', '<', now())
            ->update(['status' => 'expired']);

        $this->info("Updated {$updatedUsers} user(s) and {$updatedMemberships} membership(s)");
        return Command::SUCCESS;
    }
}
