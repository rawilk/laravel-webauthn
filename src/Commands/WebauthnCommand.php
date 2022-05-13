<?php

namespace Rawilk\Webauthn\Commands;

use Illuminate\Console\Command;

class WebauthnCommand extends Command
{
    public $signature = 'laravel-webauthn';

    public $description = 'My command';

    public function handle()
    {
        $this->comment('All done');
    }
}
