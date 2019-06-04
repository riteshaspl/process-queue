<?php
/**
 * File ProcessManager.php
 *
 * @author Edward Pfremmer <epfremme@nerdery.com>
 */
namespace Epfremme\ProcessQueue\Process;

use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\Process\Process;

/**
 * Class ProcessManager
 *
 * @package Epfremme\ProcessQueue\Process
 */
class ProcessManager
{
    /**
     * @var ProcessQueue
     */
    private $queue;

    /**
     * @var ProcessFactory
     */
    private $factory;

    /**
     * ProcessFactory constructor
     *
     * @param ProcessFactory $factory
     * @param null $limit
     */
    public function __construct(ProcessFactory $factory, $limit = null)
    {
        $this->queue = new ProcessQueue($limit);

        $this->factory = $factory;
    }

    /**
     * @param \SplFileInfo|string $cwd
     * @return PromiseInterface
     */
    public function enqueue($cmd = null, $cwd = null, $timeout = null)
    {
        $process = $this->factory->make($cmd, $cwd);

        if ($timeout) {
            $process->setTimeout($timeout);
        }

        /** @var Promise $promise */
        $promise = new Promise(function() use ($process, &$promise) {
            if ($process->isStarted()) {
                $process->wait();
            }

            $process->isSuccessful()
                ? $promise->resolve($process)
                : $promise->reject($process);
        });

        $this->queue->add($process);

        $process->setOptions([ProcessQueue::PROMISE_KEY => $promise]);

        return $promise;
    }

    public function run(\Closure $tick = null)
    {
        $queue = $this->queue;

        /** @var Process $next */
        foreach ($queue() as $next) {
            $next->start();
            $tick && $tick();
        }
    }
}
