<?php

namespace App\Validator;

use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\IsTrue;

class IsTrueV3 extends IsTrue
{
    /**
     * {@inheritdoc}
     */
    public function validatedBy(): string
    {
        return IsTrueValidatorV3::class;
    }
}
