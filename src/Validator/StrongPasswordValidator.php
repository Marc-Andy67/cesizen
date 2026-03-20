<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class StrongPasswordValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof StrongPassword) {
            throw new UnexpectedTypeException($constraint, StrongPassword::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            return;
        }

        if (mb_strlen($value) < 12) {
            $this->context->buildViolation($constraint->messageLength)
                ->addViolation();
        }

        if (!preg_match('/[A-Z]/', $value)) {
            $this->context->buildViolation($constraint->messageUpper)
                ->addViolation();
        }

        if (!preg_match('/[a-z]/', $value)) {
            $this->context->buildViolation($constraint->messageLower)
                ->addViolation();
        }

        if (!preg_match('/[0-9]/', $value)) {
            $this->context->buildViolation($constraint->messageNumber)
                ->addViolation();
        }

        if (!preg_match('/[!@#$%^&*()_+\-=\[\]{}|;:,.<>?]/', $value)) {
            $this->context->buildViolation($constraint->messageSpecial)
                ->addViolation();
        }
    }
}
