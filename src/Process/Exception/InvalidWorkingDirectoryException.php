<?php
/**
 * File InvalidWorkingDirectoryException.php
 *
 * @author Edward Pfremmer <epfremme@nerdery.com>
 */
namespace Epfremme\ProcessQueue\Process\Exception;

/**
 * Class InvalidWorkingDirectoryException
 *
 * @package Epfremme\ProcessQueue\Process\Exception
 */
class InvalidWorkingDirectoryException extends \InvalidArgumentException
{
    /**
     * InvalidWorkingDirectoryException constructor
     *
     * @param string|\SplFileInfo $directory
     */
    public function __construct($directory)
    {
        $message = sprintf('Directory %s does not exist', $directory);

        parent::__construct($message);
    }
}
