<?php

declare(strict_types=1);

namespace Sbooker\Console\Tests\Fixture;

final class SqlStateException extends \Exception
{
    public function __construct(string $sqlState)
    {
        parent::__construct('sqlstate failure');

        $this->code = $sqlState;
    }
}
