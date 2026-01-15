<?php

namespace App\Console\Commands;

use App\DDD\Shared\Domain\Bus\CommandBusInterface;
use App\DDD\TimeTracking\Application\Command\CloseOrphanTimeEntriesCommand as CloseOrphanCommand;
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
    public function handle(CommandBusInterface $commandBus): int
    {
        $this->info('Buscando fichajes huérfanos...');

        $response = $commandBus->dispatch(CloseOrphanCommand::create());

        $total = $response->totalClosed();
        $usersAffected = $response->usersAffected();

        if (0 === $total) {
            $this->info('No se encontraron fichajes huérfanos.');
        } else {
            $this->info("Cerrados {$total} fichajes huérfanos de {$usersAffected} usuarios.");
        }

        return Command::SUCCESS;
    }
}
