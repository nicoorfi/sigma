<?php

declare(strict_types=1);

namespace App\Http\Controllers\Project;

use App\Models\Project;
use Inertia\Inertia;

class SettingsController extends \App\Http\Controllers\Controller
{
    /**
     * Show project settings page
     */
    public function index(Project $project)
    {
        $cluster = $project->getAttribute('clusters')->first();
        $clusterId = null;
        $clusterState = null;

        if ($cluster !== null) {
            $clusterId = $cluster->getAttribute('id');
            $clusterState = $cluster->getAttribute('state');
        }

        return Inertia::render('project/settings/settings', [
            'clusterId' => $clusterId,
            'clusterState' => $clusterState
        ]);
    }
}