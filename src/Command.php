<?php

declare(strict_types=1);

namespace Sbooker\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Command extends \Symfony\Component\Console\Command\Command
{
    abstract protected function doExecute(InputInterface $input, OutputInterface $output): void;

    final public function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->doExecute($input, $output);
            $output->writeln('Done.');

            return 0;
        } catch (\Throwable $exception) {
            if ($output instanceof ConsoleOutputInterface) {
                $output->getErrorOutput()->writeln($exception->getMessage());
            }
            $output->writeln($exception->getMessage());
            $output->writeln($exception->getTraceAsString(), OutputInterface::OUTPUT_NORMAL | OutputInterface::VERBOSITY_VERBOSE);

            $exitCode = (int) $exception->getCode();

            return 0 === $exitCode ? 1 : $exitCode;
        }
    }
}