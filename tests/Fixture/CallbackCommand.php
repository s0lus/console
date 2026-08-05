<?php

declare(strict_types=1);

namespace Sbooker\Console\Tests\Fixture;

use Sbooker\Console\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class CallbackCommand extends Command
{
    private $callback;

    public function __construct(callable $callback)
    {
        parent::__construct('test');

        $this->callback = $callback;
    }

    protected function doExecute(InputInterface $input, OutputInterface $output): void
    {
        $callback = $this->callback;

        $callback($input, $output);
    }
}
