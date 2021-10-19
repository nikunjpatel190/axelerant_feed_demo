<?php

/*
 * This file is part of the unit testing which check feed extract command.
 *
 * (c) Nikunj Bambhroliya <nikunjpatel190@gmail.com>
 *
 */

namespace App\Tests\Command;

use App\Command\FeedExtractCommand;
use App\Repository\FeedRepository;
use App\Repository\FeedContentRepository;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class FeedExtractCommandTest extends KernelTestCase
{
    protected function setUp(): void
    {
        exec('stty 2>&1', $output, $exitcode);
        $isSttySupported = 0 === $exitcode;

        if ('Windows' === \PHP_OS_FAMILY || !$isSttySupported) {
            $this->markTestSkipped('`stty` is required to test this command.');
        }
    }

    /**
     * This test will Execute the command and assert output from database
     *
     * @author Nikunj Bambhroliya <nikunjpatel190@gmail.com>
     * @return void
     */
    public function testCreateUserNonInteractive(): void
    {
        $input = [];
        $this->executeCommand($input);
    }

    /**
     * This helper method abstracts the boilerplate code needed to test the
     * execution of a command.
     *
     * @param array $arguments All the arguments passed when executing the command
     * @param array $inputs    The (optional) answers given to the command when it asks for the value of the missing arguments
     */
    private function executeCommand(array $arguments, array $inputs = []): void
    {
        self::bootKernel();

        // this uses a special testing container that allows you to fetch private services
        $command = self::$container->get(FeedExtractCommand::class);
        $command->setApplication(new Application(self::$kernel));

        $commandTester = new CommandTester($command);
        $commandTester->setInputs($inputs);
        $commandTester->execute($arguments);
    }
}
