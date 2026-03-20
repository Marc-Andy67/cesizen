<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class StrongPassword extends Constraint
{
    public string $messageLength = 'Le mot de passe doit contenir au moins 12 caractères.';
    public string $messageUpper = 'Le mot de passe doit contenir au moins une lettre majuscule.';
    public string $messageLower = 'Le mot de passe doit contenir au moins une lettre minuscule.';
    public string $messageNumber = 'Le mot de passe doit contenir au moins un chiffre.';
    public string $messageSpecial = 'Le mot de passe doit contenir au moins un caractère spécial (!@#$%^&*()_+-=[]{}|;:,.<>?).';
}
