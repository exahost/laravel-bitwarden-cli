<?php

namespace Aleex1848\LaravelBitwardenCli\Commands;

use Illuminate\Console\Command;

class LaravelBitwardenCliCommand extends Command
{
    public $signature = 'laravel-bitwarden-cli';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
