<?php
/**
 * File ProcessFactory.php
 *
 * @author Edward Pfremmer <epfremme@nerdery.com>
 */
namespace Epfremme\ProcessQueue\Process;

use Symfony\Component\Process\Process;

/**
 * Class ProcessFactory
 *
 * @package Epfremme\ProcessQueue\Process
 */
class ProcessFactory
{
    /**
     * @var string
     */
    private $cmd;

    /**
     * ProcessFactory constructor
     *
     * @param string $cmd
     */
    public function __construct($cmd)
    {
        $this->cmd = $cmd;
    }

    /**
     * @param \SplFileInfo $directory
     * @return Process
     */
    public function make(\SplFileInfo $directory = null)
    {
        return new Process($this->cmd, $directory);
    }
}
