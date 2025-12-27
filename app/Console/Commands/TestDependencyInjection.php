<?php

namespace App\Console\Commands;

use App\DDD\User\Application\Handler\GetAllUsersWithTimeQueryHandler;
use App\DDD\RegistroHorario\Domain\RegistroHorarioRepositoryInterface;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use Illuminate\Console\Command;

class TestDependencyInjection extends Command
{
    protected $signature = 'test:di';
    protected $description = 'Test dependency injection resolution';

    public function handle(): void
    {
        try {
            $this->info('Testing dependency injection...');
            
            // Test User Repository
            $userRepo = app(UserRepositoryInterface::class);
            $this->info('✅ UserRepositoryInterface resolved: ' . get_class($userRepo));
            
            // Test RegistroHorario Repository
            $registroRepo = app(RegistroHorarioRepositoryInterface::class);
            $this->info('✅ RegistroHorarioRepositoryInterface resolved: ' . get_class($registroRepo));
            
            // Test Handler with dependencies
            $handler = app(GetAllUsersWithTimeQueryHandler::class);
            $this->info('✅ GetAllUsersWithTimeQueryHandler resolved: ' . get_class($handler));
            
            $this->info('🎉 All dependencies resolved successfully!');
            
        } catch (\Exception $e) {
            $this->error('❌ Dependency injection failed: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}
