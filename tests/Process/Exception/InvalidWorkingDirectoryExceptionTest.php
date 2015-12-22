<?php
/**
 * File InvalidWorkingDirectoryException.php
 *
 * @author Edward Pfremmer <epfremme@nerdery.com>
 */
namespace Epfremme\ProcessQueue\Tests\Process\Exception;

use Epfremme\ProcessQueue\Process\Exception\InvalidWorkingDirectoryException;

/**
 * Class InvalidWorkingDirectoryException
 *
 * @package Epfremme\ProcessQueue\Process\Exception
 */
class InvalidWorkingDirectoryExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $exception = new InvalidWorkingDirectoryException('/invalid/directory');

        $this->assertContains('/invalid/directory', $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
    }

    public function testConstructSplFileInfo()
    {
        $exception = new InvalidWorkingDirectoryException(new \SplFileInfo('/invalid/directory'));

        $this->assertContains('/invalid/directory', $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
    }
}
