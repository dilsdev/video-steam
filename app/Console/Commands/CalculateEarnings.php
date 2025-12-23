<?php

namespace App\Console\Commands;

use App\Services\EarningService;
use Illuminate\Console\Command;

class CalculateEarnings extends Command
{
    protected $signature = 'earnings:calculate {--date= : Date to calculate (YYYY-MM-DD)}';
    protected $description = 'Calculate video earnings from views';

    public function handle(EarningService $earningService): int
    {
        $date = $this->option('date') ?? now()->subDay()->toDateString();
        
        $this->info("Menghitung penghasilan untuk tanggal: {$date}");
        
        $results = $earningService->calculateDailyEarnings($date);
        
        $this->info("Diproses: " . count($results) . " video");
        
        $total = 0;
        foreach ($results as $result) {
            $this->line("  Video #{$result['video_id']}: {$result['views']} views = Rp " . number_format($result['amount']));
            $total += $result['amount'];
        }
        
        $this->info("Total penghasilan: Rp " . number_format($total));
        
        return Command::SUCCESS;
    }
}
