<?php
/**
 * File ProcessQueue.php
 *
 * @author Edward Pfremmer <epfremme@nerdery.com>
 */
namespace Epfremme\ProcessQueue\Process;

use Epfremme\Collection\Collection;
use Epfremme\ProcessQueue\System\ProcessorCounter;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\Process\Process;

/**
 * Class ProcessQueue
 *
 * @package Epfremme\ProcessQueue\Process
 */
class ProcessQueue implements \Countable
{
    const SLEEP_MICRO_SECONDS = 1000;
    const PROMISE_KEY = 'promise';

    /**
     * @var int
     */
    private $limit;

    /**
     * @var Collection
     */
    private $queue;

    /**
     * ProcessQueue constructor
     *
     * @param int $limit
     */
    public function __construct($limit = null)
    {
        if (!is_null($limit) && !is_int($limit)) {
            throw new \InvalidArgumentException(sprintf('Limit must be of type int %s given', gettype($limit)));
        }

        $this->limit = $limit ?: (new ProcessorCounter())->getCpuCount();
        $this->queue = new Collection();
    }

    /**
     * Add new process to the queue
     *
     * @param Process $process
     * @return $this
     */
    public function add(Process $process)
    {
        $this->queue->add($process);

        return $this;
    }

    /**
     * Return pending processes
     *
     * @return Collection
     */
    public function getPending()
    {
        return $this->queue->filter(function(Process $process) {
            return !$process->isStarted();
        });
    }

    /**
     * Return running processes
     *
     * @return Collection
     */
    public function getRunning()
    {
        return $this->queue->filter(function(Process $process) {
            return $process->isRunning();
        });
    }

    /**
     * Return completed processes
     *
     * @return Collection
     */
    public function getCompleted()
    {
        return $this->queue->filter(function(Process $process) {
            return $process->isTerminated();
        });
    }

    /**
     * Clear and resolve completed processes from the queue
     *
     * @return void
     */
    private function clearCompleted()
    {
        $this->getCompleted()->each(function(Process $process) {
            $process->wait();
            $this->resolve($process);
        });
    }

    /**
     * Halt execution and wait for target process to finish
     *
     * @param Process $process
     */
    public function resolve(Process $process)
    {
        $options = $process->getOptions();
        $promise = array_key_exists(self::PROMISE_KEY, $options) ? $options[self::PROMISE_KEY] : null;

        if ($promise instanceof PromiseInterface) {
            $promise->wait(false);
        }

        $this->queue->remove($process);
    }

    /**
     * Run the queue
     *
     * @return \Generator
     */
    public function __invoke()
    {
        while (!$this->queue->isEmpty()) {
            usleep(self::SLEEP_MICRO_SECONDS);

            $pending = $this->getPending();

            if ($pending->count() && $this->getRunning()->count() < $this->limit) {
                yield $pending->shift();
            } else {
                yield new NullProcess();
            }

            $this->clearCompleted();
        }
    }

    /**
     * Return queue count
     *
     * @return int
     */
    public function count()
    {
        return $this->queue->count();
    }
}
