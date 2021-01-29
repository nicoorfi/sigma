<?php

declare(strict_types=1);

namespace App\Jobs\Indexing;

use App\Models\IndexingPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ExecuteIndexingPlan implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public int $planId)
    {
        $this->queue = 'long-running-queue';
    }

    public function handle(): void
    {
        $plan = IndexingPlan::find($this->planId);

        ray('handled')->green();

        $plan->update(['state' => IndexingPlan::NO_STATE]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $throwable)
    {
        throw $throwable;
    }
}
