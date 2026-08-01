<?php

declare(strict_types=1);

namespace Sbooker\Console\Tests;

use PHPUnit\Framework\TestCase;
use Sbooker\Console\Command;
use Sbooker\Console\Tests\Fixture\CallbackCommand;
use Sbooker\Console\Tests\Fixture\SqlStateException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

final class CommandTest extends TestCase
{
    public function testReturnsZeroOnSuccess(): void
    {
        $tester = $this->runCommand(function (): void {
        });

        self::assertSame(0, $tester->getStatusCode());
    }

    public function testReportsDoneOnSuccess(): void
    {
        $tester = $this->runCommand(function (): void {
        });

        self::assertStringContainsString('Done.', $tester->getDisplay());
    }

    public function testWritesCommandOutput(): void
    {
        $tester = $this->runCommand(function ($input, OutputInterface $output): void {
            $output->writeln('payload');
        });

        self::assertStringContainsString('payload', $tester->getDisplay());
    }

    public function testReturnsOneWhenExceptionCodeIsZero(): void
    {
        $tester = $this->runCommand(function (): void {
            throw new \RuntimeException('no code');
        });

        self::assertSame(1, $tester->getStatusCode());
    }

    public function testReturnsExceptionCode(): void
    {
        $tester = $this->runCommand(function (): void {
            throw new \RuntimeException('coded', 42);
        });

        self::assertSame(42, $tester->getStatusCode());
    }

    public function testCastsNumericStringExceptionCodeToInt(): void
    {
        $tester = $this->runCommand(function (): void {
            throw new SqlStateException('23505');
        });

        self::assertSame(23505, $tester->getStatusCode());
    }

    public function testFallsBackToOneWhenExceptionCodeIsNotNumeric(): void
    {
        $tester = $this->runCommand(function (): void {
            throw new SqlStateException('HY000');
        });

        self::assertSame(1, $tester->getStatusCode());
    }

    public function testCatchesErrorsAsWellAsExceptions(): void
    {
        $tester = $this->runCommand(function (): void {
            throw new \Error('engine level');
        });

        self::assertSame(1, $tester->getStatusCode());
        self::assertStringContainsString('engine level', $tester->getDisplay());
    }

    public function testDoesNotReportDoneOnFailure(): void
    {
        $tester = $this->runCommand(function (): void {
            throw new \RuntimeException('failed');
        });

        self::assertStringNotContainsString('Done.', $tester->getDisplay());
    }

    public function testWritesExceptionMessageToStdout(): void
    {
        $tester = $this->runCommand(
            function (): void {
                throw new \RuntimeException('the message');
            },
            ['capture_stderr_separately' => true]
        );

        self::assertStringContainsString('the message', $tester->getDisplay());
    }

    public function testWritesExceptionMessageToStderr(): void
    {
        $tester = $this->runCommand(
            function (): void {
                throw new \RuntimeException('the message');
            },
            ['capture_stderr_separately' => true]
        );

        self::assertStringContainsString('the message', $tester->getErrorOutput());
    }

    public function testHidesStackTraceByDefault(): void
    {
        $tester = $this->runCommand(function (): void {
            throw new \RuntimeException('failed');
        });

        self::assertStringNotContainsString('#0', $tester->getDisplay());
    }

    public function testWritesStackTraceInVerboseMode(): void
    {
        $tester = $this->runCommand(
            function (): void {
                throw new \RuntimeException('failed');
            },
            ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]
        );

        self::assertStringContainsString('#0', $tester->getDisplay());
    }

    public function testExecuteDeclaresIntReturnType(): void
    {
        $returnType = (new \ReflectionMethod(Command::class, 'execute'))->getReturnType();

        self::assertNotNull($returnType);
        self::assertSame('int', $returnType->getName());
    }

    public function testExecuteIsFinalSoSubclassesCannotBypassTheWrapper(): void
    {
        self::assertTrue((new \ReflectionMethod(Command::class, 'execute'))->isFinal());
    }

    private function runCommand(callable $callback, array $options = []): CommandTester
    {
        $tester = new CommandTester(new CallbackCommand($callback));
        $tester->execute([], $options);

        return $tester;
    }
}
