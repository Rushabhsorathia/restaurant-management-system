<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\SampleQueueJob;
use Illuminate\Console\Command;

class DispatchSampleJob extends Command
{
    protected $signature = 'rms:dispatch-sample-job {message=Greetings from RMS!}';

    protected $description = 'Dispatch a sample queue job to verify Horizon is wired up.';

    public function handle(): int
    {
        SampleQueueJob::dispatch($this->argument('message'));

        $this->info('SampleQueueJob dispatched to the queue.');

        return self::SUCCESS;
    }
}
