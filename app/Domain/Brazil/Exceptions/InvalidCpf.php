<?php

declare(strict_types=1);

namespace Fonid\Domain\Brazil\Exceptions;

use InvalidArgumentException;

/**
 * The message never includes the rejected value: it is personal data.
 */
final class InvalidCpf extends InvalidArgumentException
{
    public function __construct()
    {
        parent::__construct('The given value is not a valid CPF.');
    }
}
