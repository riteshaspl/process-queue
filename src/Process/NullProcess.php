<?php
/**
 * File NullProcess.php
 *
 * @author Edward Pfremmer <epfremme@nerdery.com>
 */
namespace Epfremme\ProcessQueue\Process;

use Symfony\Component\Process\Process;

/**
 * Class NullProcess
 *
 * @package Epfremme\ProcessQueue\Process
 */
class NullProcess extends Process
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        // do nothing
    }

    /**
     * {@inheritdoc}
     */
    public function start($callback = null)
    {
        // do nothing
    }

    /**
     * {@inheritdoc}
     */
    public function run($callback = null)
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function wait($callback = null)
    {
        return 0;
    }
}
