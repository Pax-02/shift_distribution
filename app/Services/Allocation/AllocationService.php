<?php

namespace App\Services\Allocation;

use App\Models\Shift;
use App\Models\Worker;
use App\Rules\Allocation\AllocationRuleInterface;
use Illuminate\Support\Collection;

class AllocationService
{
    /**
     * Loads active workers, filters out anyone failing a hard constraint,
     * scores the rest against the soft rules, and returns them ranked
     * highest-score-first with a full per-rule breakdown.
     *
     * @return Collection<int, WorkerAllocationScore>
     */
    public function allocate(Shift $shift): Collection
    {
        $rules = $this->enabledRules();

        // Eager-load everything the rule set reads, so scoring N workers
        // against 1 shift doesn't fire N queries per rule.
        $workers = Worker::query()
            ->with(['availability', 'hours', 'assignments.shift.destinationAddress'])
            ->get();

        return $workers
            ->map(fn (Worker $worker) => $this->scoreWorker($worker, $shift, $rules))
            ->filter() // hard-excluded workers scored to null
            ->sortByDesc(fn (WorkerAllocationScore $score) => $score->totalScore)
            ->values();
    }

    /** @param  Collection<int, AllocationRuleInterface>  $rules */
    private function scoreWorker(Worker $worker, Shift $shift, Collection $rules): ?WorkerAllocationScore
    {
        $breakdown = [];
        $total = 0.0;

        foreach ($rules as $rule) {
            $ruleClass = $rule::class;

            if ($rule->isHardConstraint()) {
                $passed = $rule->passes($worker, $shift);

                $breakdown[] = [
                    'rule' => $ruleClass,
                    'type' => 'hard',
                    'passed' => $passed,
                    'explanation' => $rule->explain($worker, $shift),
                ];

                if (! $passed) {
                    return null;
                }

                continue;
            }

            $weight = (float) (config("allocation.rules.{$ruleClass}.weight") ?? 0);
            $rawScore = $rule->score($worker, $shift);
            $weightedScore = $rawScore * $weight;
            $total += $weightedScore;

            $breakdown[] = [
                'rule' => $ruleClass,
                'type' => 'soft',
                'weight' => $weight,
                'raw_score' => $rawScore,
                'weighted_score' => $weightedScore,
                'explanation' => $rule->explain($worker, $shift),
            ];
        }

        return new WorkerAllocationScore($worker->id, $total, $breakdown);
    }

    /** @return Collection<int, AllocationRuleInterface> */
    private function enabledRules(): Collection
    {
        return collect(config('allocation.rules'))
            ->filter(fn (array $config) => $config['enabled'] ?? true)
            ->keys()
            ->map(fn (string $ruleClass) => app($ruleClass));
    }
}
