<?php

namespace App\Validator\Constraint;

use App\Validator\MaxSellValidator;
use Symfony\Component\Validator\Constraint;

#[\Attribute]
class MaxSell extends Constraint
{
    public function validatedBy(): string
    {
        return MaxSellValidator::class;
    }
}