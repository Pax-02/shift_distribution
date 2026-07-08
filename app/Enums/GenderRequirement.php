<?php

namespace App\Enums;

enum GenderRequirement: string
{
    case Any = 'any';
    case Male = 'male';
    case Female = 'female';

    /** Whether a worker of the given gender satisfies this requirement. */
    public function satisfiedBy(Gender $workerGender): bool
    {
        return match ($this) {
            self::Any => true,
            self::Male => $workerGender === Gender::Male,
            self::Female => $workerGender === Gender::Female,
        };
    }
}
