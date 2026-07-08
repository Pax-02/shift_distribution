<?php

namespace App\Rules\Allocation;

use App\Models\Shift;
use App\Models\Worker;

/**
 * One independent unit of allocation logic. AllocationService treats every
 * rule uniformly (SOLID: open/closed — new rules plug in without touching
 * the service), distinguishing behaviour only via isHardConstraint().
 */
interface AllocationRuleInterface
{
    /**
     * Hard constraints exclude a worker outright when passes() is false
     * (e.g. gender mismatch, inactive). Soft rules only ever contribute a
     * weighted score and never exclude anyone.
     */
    public function isHardConstraint(): bool;

    /**
     * Eligibility check for hard constraints. Always true for soft-scoring
     * rules — they don't gate eligibility, so AllocationService never calls
     * this for them, but the contract stays uniform either way.
     */
    public function passes(Worker $worker, Shift $shift): bool;

    /**
     * Normalized contribution in the 0.0–1.0 range; only called for workers
     * that already passed every hard constraint. AllocationService multiplies
     * this by the rule's configured weight.
     */
    public function score(Worker $worker, Shift $shift): float;

    /** Human-readable reason, surfaced in the score breakdown for transparency. */
    public function explain(Worker $worker, Shift $shift): string;
}
