<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RefreshDatabaseWithSeeders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:refresh-seed
                            {--force : Разрешить выполнение в production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Пересоздать базу (migrate:fresh) и выполнить сидеры DatabaseSeeder';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (app()->environment('production') && ! $this->option('force')) {
            $this->error('В production добавьте флаг --force для подтверждения полного сброса БД.');

            return self::FAILURE;
        }

        $this->info('Выполняю migrate:fresh --seed...');

        $this->call('migrate:fresh', [
            '--seed' => true,
            '--force' => true,
        ]);

        $this->info('Готово: миграции и сидеры выполнены.');

        return self::SUCCESS;
    }
}
