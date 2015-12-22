<?php
/**
 * File NullProcessTest.php
 *
 * @author Edward Pfremmer <epfremme@nerdery.com>
 */
namespace Epfremme\ProcessQueue\Tests\Process;

use Epfremme\ProcessQueue\Process\NullProcess;
use Symfony\Component\Process\Process;

/**
 * Class NullProcessTest
 *
 * @package Epfremme\ProcessQueue\Tests\Process
 */
class NullProcessTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $process = new NullProcess();

        $this->assertInstanceOf(Process::class, $process);
    }

    public function testStart()
    {
        $process = new NullProcess();

        $this->assertNull($process->start());
    }

    public function testRun()
    {
        $process = new NullProcess();

        $this->assertEquals(0, $process->run());
    }

    public function testWait()
    {
        $process = new NullProcess();

        $this->assertEmpty(0, $process->wait());
    }
}
