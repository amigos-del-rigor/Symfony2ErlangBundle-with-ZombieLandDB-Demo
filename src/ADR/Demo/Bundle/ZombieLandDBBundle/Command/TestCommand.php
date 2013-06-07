<?php
namespace ADR\Demo\Bundle\ZombieLandDBBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class TestCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('zldb:test')
            ->setDescription('Test ZombieLandDB.')
            ->setHelp(<<<EOT
The <info>zldb:test</info> command tests ZombieLandDB.
EOT
            )->addArgument('totalProcesses', InputArgument::OPTIONAL, 'Total process number', 10000);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $totalProcesses = $input->getArgument('totalProcesses');

        $node = $this->getContainer()->get('adr_symfony2erlang.channel.manager')->getChannel('peb_node0');
        $pids = array();

        // Test 1: Create processes
        $ts0 = microtime(true);
        for($i=1; $i <= $totalProcesses; $i++)
        {
            list($rOk, $pid) = $node->call('zldb_manager', 'get_pid_from_id', array('abc' . $i));
            $pids[$i] = $pid;
        }
        $output->writeln('Elapsed time to create ' . ($i - 1) . ' processes: ' . (microtime(true) - $ts0));

        // Test 2: Set data
        $ts0 = microtime(true);
        for($i=1; $i <= $totalProcesses; $i++)
        {
            $rOk = $node->call('zldb_entity', 'set', array($pids[$i], 'foo', 'data foo'));
        }
        $output->writeln('Elapsed time to set ' . ($i - 1) . ' data: ' . (microtime(true) - $ts0));

        // Test 3: Get data
        $ts0 = microtime(true);
        for($i=1; $i <= $totalProcesses; $i++)
        {
            $data = $node->call('zldb_entity', 'get', array($pids[$i], 'foo'));
        }
        $output->writeln('Elapsed time to get ' . ($i - 1) . ' data: ' . (microtime(true) - $ts0));
    }
}