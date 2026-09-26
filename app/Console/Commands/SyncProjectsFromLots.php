<?php

namespace App\Console\Commands;

use App\Models\Lot;
use App\Models\Project;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('projects:sync')]
#[Description('Create a Project for every distinct subdivision string, then link existing lots to it')]
class SyncProjectsFromLots extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $subdivisions = Lot::query()->whereNull('project_id')->distinct()->pluck('subdivision')->filter();

        $created = 0;
        $linked = 0;

        foreach ($subdivisions as $name) {
            $project = Project::firstOrCreate(['name' => $name]);
            $created += $project->wasRecentlyCreated ? 1 : 0;

            $linked += Lot::where('subdivision', $name)
                ->whereNull('project_id')
                ->update(['project_id' => $project->id]);
        }

        $this->info("Created {$created} project(s), linked {$linked} lot(s).");

        return self::SUCCESS;
    }
}
