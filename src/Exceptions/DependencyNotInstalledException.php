<?php

declare(strict_types=1);

namespace Ritechoice23\Worldable\Exceptions;

use Exception;

class DependencyNotInstalledException extends Exception
{
    public static function forRelationship(string $modelClass, string $relationship, string $missingComponent): self
    {
        $message = "Cannot access '{$relationship}' relationship on {$modelClass}.\n\n";
        $message .= "The '{$missingComponent}' component is not installed.\n\n";
        $message .= "To resolve this:\n";
        $message .= "  1. Install the component: php artisan world:install --{$missingComponent}\n";
        $message .= "  2. Link existing data: php artisan world:link\n";
        $message .= "  3. Check status: php artisan world:health\n";

        return new self($message);
    }
}
