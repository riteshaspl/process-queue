<?php
/**
 * File ProcessManagerTest.php
 *
 * @author Edward Pfremmer <epfremme@nerdery.com>
 */
namespace Epfremme\ProcessQueue\Tests\Process;

use Epfremme\ProcessQueue\Process\ProcessFactory;
use Epfremme\ProcessQueue\Process\ProcessManager;
use Epfremme\ProcessQueue\Process\ProcessQueue;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\Process\Process;

/**
 * Class ProcessManagerTest
 *
 * @package Epfremme\ProcessQueue\Tests\Process
 */
class ProcessManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProcessFactory
     */
    private $factory;

    /**
     * @var \SplFileInfo|\Mockery\MockInterface
     */
    private $directory;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->factory = new ProcessFactory('pwd');
        $this->directory = \Mockery::mock(\SplFileInfo::class);
    }

    public function testConstruct()
    {
        $processManager = new ProcessManager($this->factory);

        $this->assertAttributeSame($this->factory, 'factory', $processManager);
        $this->assertAttributeInstanceOf(ProcessQueue::class, 'queue', $processManager);
    }

    public function testConstructWithLimit()
    {
        $processManager = new ProcessManager($this->factory, 4);
        $expectedQueue = new ProcessQueue(4);

        $this->assertAttributeEquals($expectedQueue, 'queue', $processManager);
    }

    public function testEnqueue()
    {
        $processManager = new ProcessManager($this->factory);
        $promise = $processManager->enqueue($this->directory);

        $this->assertAttributeCount(1, 'queue', $processManager);
        $this->assertInstanceOf(PromiseInterface::class, $promise);
    }

    public function testEnqueueWithoutCwd()
    {
        $processManager = new ProcessManager($this->factory);
        $promise = $processManager->enqueue();

        $this->assertAttributeCount(1, 'queue', $processManager);
        $this->assertInstanceOf(PromiseInterface::class, $promise);
    }

    public function testEnqueueFulfilledPromise()
    {
        $processManager = new ProcessManager($this->factory);
        $promise = $processManager->enqueue($this->directory);

        $resolved = false;
        $promise->then(function() use (&$resolved) {
            $this->assertInstanceOf(Process::class, func_get_arg(0));
            $resolved = true;
        });

        $processManager->run();

        $this->assertTrue($resolved);
    }

    public function testEnqueueRejectedPromise()
    {
        $processFactory = new ProcessFactory('noop');
        $processManager = new ProcessManager($processFactory);
        $promise = $processManager->enqueue($this->directory);

        $rejected = false;
        $promise->otherwise(function() use (&$rejected) {
            $this->assertInstanceOf(Process::class, func_get_arg(0));
            $rejected = true;
        });

        $processManager->run();

        $this->assertTrue($rejected);
    }

    /** @depends testEnqueue */
    public function testRun()
    {
        $processManager = new ProcessManager($this->factory);
        $promise = $processManager->enqueue($this->directory);

        $promise->then(function($process) {
            /** @var Process $process */
            $this->assertInstanceOf(Process::class, $process);
            $this->assertArrayHasKey(ProcessQueue::PROMISE_KEY, $process->getOptions());

            /** @var PromiseInterface $promise */
            $promise = $process->getOptions()[ProcessQueue::PROMISE_KEY];
            $this->assertInstanceOf(PromiseInterface::class, $promise);
            $this->assertTrue($process->isTerminated());
            $this->assertTrue($process->isStarted());
            $this->assertFalse($process->isRunning());
            $this->assertEquals(PromiseInterface::FULFILLED, $promise->getState());
        });

        $processManager->run();
    }

    /** @depends testEnqueue */
    public function testRunWithTick()
    {
        $processManager = new ProcessManager($this->factory);
        $processManager->enqueue($this->directory);

        $ticks = 0;
        $processManager->run(function() use (&$ticks) {
            $ticks++;
        });

        $this->assertGreaterThan(0, $ticks);
    }
}
