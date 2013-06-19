<?php
namespace ADR\Demo\Bundle\ZombieLandDBBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class TestCommand extends ContainerAwareCommand
{
    const get_pid_from_id   = "[~s]";
    const set               = "[~P, ~s, ~s]";
    const get               = "[~P, ~s]";
    const setPidList        = "[~s, ~s, ~s]";
    protected $totalProcesses;
    protected $node;
    protected $pids = array();

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
        $this->totalProcesses = $input->getArgument('totalProcesses');

        $this->node = $this->getContainer()->get('adr_symfony2erlang.channel.manager')->getChannel('peb_node0');

        $output->writeln($this->process('getPidFromId'));
        $output->writeln($this->process('set'));
        $output->writeln($this->process('get'));
    }

    protected function process($method)
    {
        // Test 1: Create processes
        $ts0 = microtime(true);
        $initPartial = $ts0;
        for($i=1; $i <= $this->totalProcesses; $i++)
        {
            $data = $this->$method($i);
            if ($method === 'get' && !is_null($data)) {
                if (!isset($data[1])) {
                    throw new \Exception('no valid result found');
                }
            }
        }

        return 'Elapsed time to create ' . ($i - 1) . ' processes: ' . (microtime(true) - $ts0);
    }

    protected function getPidFromId($i)
    {
        list($rOk, $pid) = $this->node->call('zldb_manager', 'get_pid_from_id', array(self::get_pid_from_id ,array('abc' . $i)));
        $this->pids[$i] = $pid;
    }

    protected function set($i)
    {
        $rOk = $this->node->call('zldb_entity', 'set', array(self::set, array($this->pids[$i], 'foo', 'data foo-'.$i)));
    }

    protected function get($i)
    {
        $data = $this->node->call('zldb_entity', 'get', array(self::get, array($this->pids[$i], 'foo')));

        return ($i % 25000 === 0) ? $data: null;
    }
}