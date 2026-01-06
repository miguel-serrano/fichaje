<?php

namespace App\Console\Commands;

use App\DDD\TimeTracking\Services\TimeTrackingService;
use Illuminate\Console\Command;

class CloseOrphanTimeEntriesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'time-entries:close-orphans';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cierra fichajes huérfanos de días anteriores';

    /**
     * Execute the console command.
     */
    public function handle(TimeTrackingService $service): int
    {
        $this->info('Buscando fichajes huérfanos...');

        $result = $service->closeOrphanTimeEntries();

        $total = array_sum(array_map('count', $result));
        $usersAffected = count($result);

        if ($total === 0) {
            $this->info('No se encontraron fichajes huérfanos.');
        } else {
            $this->info("Cerrados {$total} fichajes huérfanos de {$usersAffected} usuarios.");
        }

        return Command::SUCCESS;
    }
}
