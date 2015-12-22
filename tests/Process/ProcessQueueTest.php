<?php
/**
 * File ProcessQueueTest.php
 *
 * @author Edward Pfremmer <epfremme@nerdery.com>
 */
namespace Epfremme\ProcessQueue\Tests\Process;

use Epfremme\Collection\Collection;
use Epfremme\ProcessQueue\System\ProcessorCounter;
use Epfremme\ProcessQueue\Process\ProcessQueue;
use GuzzleHttp\Promise\Promise;
use Symfony\Component\Process\Process;

/**
 * Class ProcessQueueTest
 *
 * @package Epfremme\ProcessQueue\Tests\Process
 */
class ProcessQueueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Add optional promise to process options
     *
     * @param Process $process
     * @return Promise
     */
    private function addPromise(Process $process)
    {
        /** @var Promise $promise */
        $promise = new Promise(function() use ($process, &$promise) {
            $process->wait();
            $promise->resolve($process);
        });

        $process->setOptions([ProcessQueue::PROMISE_KEY => $promise]);

        return $promise;
    }

    public function testConstruct()
    {
        $queue = new ProcessQueue();
        $counter = new ProcessorCounter();

        $this->assertInstanceOf(\Countable::class, $queue);
        $this->assertAttributeEquals($counter->getCpuCount(), 'limit', $queue);
    }

    public function testConstructWithArgs()
    {
        $queue = new ProcessQueue(4);

        $this->assertInstanceOf(\Countable::class, $queue);
        $this->assertAttributeEquals(4, 'limit', $queue);
    }

    /** @expectedException \InvalidArgumentException */
    public function testConstructException()
    {
        new ProcessQueue([new \ArrayObject()]);
    }

    public function testAdd()
    {
        $queue = new ProcessQueue();
        $process = new Process('pwd');

        $queue->add($process);

        $this->assertNotEmpty($queue);
        $this->assertCount(1, $queue);
    }

    /** @depends testAdd */
    public function testGetPending()
    {
        $queue = new ProcessQueue();
        $process = new Process('pwd');

        $queue->add($process);

        $pending = $queue->getPending();

        $this->assertInstanceOf(Collection::class, $pending);
        $this->assertContainsOnly(Process::class, $pending);
        $this->assertInstanceOf(Process::class, $pending->get(0));
        $this->assertSame($process, $pending->get(0));
    }

    /** @depends testAdd */
    public function testGetRunning()
    {
        $queue = new ProcessQueue();
        $process = new Process('sleep 0.1');

        $queue->add($process);

        $this->assertEmpty($queue->getRunning());

        $process->start();

        $running = $queue->getRunning();

        $this->assertInstanceOf(Collection::class, $running);
        $this->assertContainsOnly(Process::class, $running);
        $this->assertInstanceOf(Process::class, $running->get(0));
        $this->assertSame($process, $running->get(0));
    }

    /** @depends testAdd */
    public function testGetCompleted()
    {
        $queue = new ProcessQueue();
        $process = new Process('pwd');

        $queue->add($process);

        $this->assertEmpty($queue->getCompleted());

        $process->run();

        $completed = $queue->getCompleted();

        $this->assertInstanceOf(Collection::class, $completed);
        $this->assertContainsOnly(Process::class, $completed);
        $this->assertInstanceOf(Process::class, $completed->get(0));
        $this->assertSame($process, $completed->get(0));
    }

    /** @depends testAdd */
    public function testResolve()
    {
        $queue = new ProcessQueue();
        $process = new Process('pwd');

        $queue->add($process);
        $process->run();
        $queue->resolve($process);

        $this->assertEmpty($queue);
        $this->assertCount(0, $queue);
        $this->assertTrue($process->isTerminated());
    }

    /** @depends testAdd */
    public function testResolveWithPromise()
    {
        $queue = new ProcessQueue();
        $process = new Process('pwd');
        $promise = $this->addPromise($process);

        $isResolved = false;
        $promise->then(function() use (&$isResolved) {
            $isResolved = true;
        });

        $this->assertFalse($isResolved);

        $queue->add($process);
        $process->run();
        $queue->resolve($process);

        $this->assertEmpty($queue);
        $this->assertCount(0, $queue);
        $this->assertTrue($isResolved);
        $this->assertTrue($process->isTerminated());
    }

    /** @depends testAdd */
    public function testInvoke()
    {
        $queue = new ProcessQueue();
        $process = new Process('pwd');

        $queue->add($process);

        /** @var Process $pending */
        foreach ($queue() as $pending) {
            $this->assertInstanceOf(Process::class, $pending);
            $this->assertFalse($pending->isStarted());

            $pending->start();
        }

        $this->assertEmpty($queue);
        $this->assertCount(0, $queue);
        $this->assertTrue($process->isTerminated());
    }
}
