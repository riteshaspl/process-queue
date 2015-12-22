<?php
/**
 * File ProcessFactoryTest.php
 *
 * @author Edward Pfremmer <epfremme@nerdery.com>
 */
namespace Epfremme\ProcessQueue\Tests\Process;

use Epfremme\ProcessQueue\Process\ProcessFactory;
use Symfony\Component\Process\Process;

/**
 * Class ProcessFactoryTest
 *
 * @package Epfremme\ProcessQueue\Tests\Process
 */
class ProcessFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $factory = new ProcessFactory('pwd');

        $this->assertAttributeEquals('pwd', 'cmd', $factory);
    }

    public function testMake()
    {
        $factory = new ProcessFactory('pwd');

        /** @var \SplFileInfo|\Mockery\MockInterface $fileinfo */
        $fileinfo = \Mockery::mock(\SplFileInfo::class);
        $fileinfo->shouldReceive('__toString')->andReturn('/tmp');

        $process = $factory->make($fileinfo);

        $this->assertInstanceOf(Process::class, $process);
        $this->assertEquals('pwd', $process->getCommandLine());
        $this->assertSame($fileinfo, $process->getWorkingDirectory());
        $this->assertFalse($process->isStarted());
        $this->assertFalse($process->isRunning());
    }

    /** @expectedException \Epfremme\ProcessQueue\Process\Exception\InvalidWorkingDirectoryException */
    public function testMakeException()
    {
        $factory = new ProcessFactory('pwd');

        $factory->make('/invalid/directory');
    }
}
