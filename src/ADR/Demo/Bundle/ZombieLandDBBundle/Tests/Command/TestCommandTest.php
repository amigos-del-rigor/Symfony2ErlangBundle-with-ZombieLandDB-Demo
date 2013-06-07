<?php

namespace ADR\Bundle\Symfony2ErlangBundle\Tests\Service\Rest;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use ADR\Demo\Bundle\ZombieLandDBBundle\Command\TestCommand;

class TestCommandTest extends WebTestCase
{
    public function testExecute()
    {
        $kernel = $this->createKernel();
        $kernel->boot();

        $application = new Application($kernel);
        $application->add(new TestCommand());

        $command = $application->find('zldb:test');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array('command' => $command->getName(), 'totalProcesses'=> 1000));
        $result = $commandTester->getDisplay();

        $this->assertRegExp('/Elapsed time to create 1000 processes/', $result);
        $this->assertRegExp('/Elapsed time to set 1000 data/', $result);
        $this->assertRegExp('/Elapsed time to get 1000 data/', $result);
    }
}