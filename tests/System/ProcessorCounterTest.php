<?php
/**
 * File ProcessorCounterTest.php
 *
 * @author Edward Pfremmer <epfremme@nerdery.com>
 */
namespace Epfremme\ProcessQueue\Tests\System;

use Epfremme\ProcessQueue\System\ProcessorCounter;

/**
 * Class ProcessorCounterTest
 *
 * @package Epfremme\ProcessQueue\Tests\System
 */
class ProcessorCounterTest extends \PHPUnit_Framework_TestCase
{
    public function testGetCpuCount()
    {
        $processorCounter = new ProcessorCounter();
        $processorCount = $processorCounter->getCpuCount();

        $this->assertInternalType('integer', $processorCount);
        $this->assertGreaterThan(0, $processorCount);
    }

    public function testToString()
    {
        $processorCounter = new ProcessorCounter();
        $processorCount = (string) $processorCounter;

        $this->assertInternalType('string', $processorCount);
        $this->assertGreaterThan(0, $processorCount);
    }
}
