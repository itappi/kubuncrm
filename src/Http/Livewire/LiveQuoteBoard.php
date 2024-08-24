<?php

namespace VentureDrake\LaravelCrm\Http\Livewire;

use Illuminate\Support\Collection;
use VentureDrake\LaravelCrm\Http\Livewire\KanbanBoard\KanbanBoard;
use VentureDrake\LaravelCrm\Models\Quote;
use VentureDrake\LaravelCrm\Models\Pipeline;

class LiveQuoteBoard extends KanbanBoard
{
    public $model = 'deal';

    public function stages(): Collection
    {
        if($pipeline = Pipeline::where('model', get_class(new Quote()))->first()) {
            return $pipeline->pipelineStages()
                ->orderBy('order')
                ->orderBy('id')
                ->get();
        }
    }

    public function onStageChanged($recordId, $stageId, $fromOrderedIds, $toOrderedIds)
    {
        Quote::find($recordId)->update([
            'pipeline_stage_id' => $stageId
        ]);
    }

    public function records(): Collection
    {
        return Quote::get()
            ->map(function (Quote $quote) {
                return [
                    'id' => $quote->id,
                    'title' => $quote->title,
                    'labels' => $quote->labels,
                    'stage' => $quote->pipelineStage->id ?? $this->firstStageId(),
                ];
            });
    }
}
