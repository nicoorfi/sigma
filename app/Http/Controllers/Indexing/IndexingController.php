<?php

declare(strict_types=1);

namespace App\Http\Controllers\Indexing;

use App\Models\IndexingPlan;
use App\Models\IndexingPlanDetails;
use App\Models\IndexingType;
use App\Models\Project;
use Database\Factories\IndexingPlanFactory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IndexingController extends \App\Http\Controllers\Controller
{
    public function __invoke(Project $project, Request $request)
    {
        $this->authorize('view', $project);

        $plans = IndexingPlan::where('project_id', $project->id)
            ->with('type')
            ->get()
            ->map(fn ($plan) => $plan->only([
                'id',
                'name',
                'description',
                'state',
                'type_type',
                'ping_url',
                'deactivated_at',
                'created_at',
                'type',
                'updated_at',
                'run_at',
            ]));

        // User should have at least one cluster before coming to the indexing view.
        // RedirectToClusterCreateIfHasntCluster::class takes care of that
        $clusterId = $project->clusters->first()->id;

        return Inertia::render('indexing/indexing', ['plans' => $plans, 'clusterId' => $clusterId]);
    }
}