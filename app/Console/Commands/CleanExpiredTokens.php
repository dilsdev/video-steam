<?php

namespace App\Console\Commands;

use App\Models\VideoToken;
use Illuminate\Console\Command;

class CleanExpiredTokens extends Command
{
    protected $signature = 'tokens:clean';
    protected $description = 'Clean expired video tokens';

    public function handle(): int
    {
        $deleted = VideoToken::where('expires_at', '<', now())->delete();
        $this->info("Deleted {$deleted} expired tokens");
        return Command::SUCCESS;
    }
}
