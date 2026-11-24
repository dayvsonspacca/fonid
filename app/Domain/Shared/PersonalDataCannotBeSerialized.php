<?php

declare(strict_types=1);

namespace Fonid\Domain\Shared;

use LogicException;

final class PersonalDataCannotBeSerialized extends LogicException
{
    /**
     * @param class-string<PersonalData> $class
     */
    public static function for(string $class): self
    {
        return new self("{$class} holds personal data and cannot be serialized. Pass its value explicitly instead.");
    }
}
