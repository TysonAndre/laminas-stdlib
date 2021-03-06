<?php

/**
 * @see       https://github.com/laminas/laminas-stdlib for the canonical source repository
 * @copyright https://github.com/laminas/laminas-stdlib/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-stdlib/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Stdlib;

use Laminas\Stdlib\PriorityQueue;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Stdlib
 */
class PriorityQueueTest extends TestCase
{
    /**
     * @var PriorityQueue
     */
    protected $queue;

    public function setUp()
    {
        $this->queue = new PriorityQueue();
        $this->queue->insert('foo', 3);
        $this->queue->insert('bar', 4);
        $this->queue->insert('baz', 2);
        $this->queue->insert('bat', 1);
    }

    public function testSerializationAndDeserializationShouldMaintainState()
    {
        $s = serialize($this->queue);
        $unserialized = unserialize($s);
        $count = count($this->queue);
        $this->assertSame(
            $count,
            count($unserialized),
            'Expected count ' . $count . '; received ' . count($unserialized)
        );

        $expected = iterator_to_array($this->queue);
        $test = iterator_to_array($unserialized);
        $this->assertSame(
            $expected,
            $test,
            'Expected: ' . var_export($expected, 1) . "\nReceived:" . var_export($test, 1)
        );
    }

    public function testRetrievingQueueAsArrayReturnsDataOnlyByDefault()
    {
        $expected = [
            'foo',
            'bar',
            'baz',
            'bat',
        ];
        $test     = $this->queue->toArray();
        $this->assertSame($expected, $test, var_export($test, 1));
    }

    public function testCanCastToArrayOfPrioritiesOnly()
    {
        $expected = [
            3,
            4,
            2,
            1,
        ];
        $test     = $this->queue->toArray(PriorityQueue::EXTR_PRIORITY);
        $this->assertSame($expected, $test, var_export($test, 1));
    }

    public function testCanCastToArrayOfDataPriorityPairs()
    {
        $expected = [
            ['data' => 'foo', 'priority' => 3],
            ['data' => 'bar', 'priority' => 4],
            ['data' => 'baz', 'priority' => 2],
            ['data' => 'bat', 'priority' => 1],
        ];
        $test     = $this->queue->toArray(PriorityQueue::EXTR_BOTH);
        $this->assertSame($expected, $test, var_export($test, 1));
    }

    public function testCanIterateMultipleTimesAndReceiveSameResults()
    {
        $expected = ['bar', 'foo', 'baz', 'bat'];

        for ($i = 1; $i < 3; $i++) {
            $test = [];
            foreach ($this->queue as $item) {
                $test[] = $item;
            }
            $this->assertEquals($expected, $test, 'Failed at iteration ' . $i);
        }
    }

    public function testCanRemoveItemFromQueue()
    {
        $this->queue->remove('baz');
        $expected = ['bar', 'foo', 'bat'];
        $test = array_values(iterator_to_array($this->queue));
        $this->assertEquals($expected, $test);
    }

    public function testCanTestForExistenceOfItemInQueue()
    {
        $this->assertTrue($this->queue->contains('foo'));
        $this->assertFalse($this->queue->contains('foobar'));
    }

    public function testCanTestForExistenceOfPriorityInQueue()
    {
        $this->assertTrue($this->queue->hasPriority(3));
        $this->assertFalse($this->queue->hasPriority(1000));
    }

    public function testCloningAlsoClonesQueue()
    {
        $foo  = new \stdClass();
        $foo->name = 'bar';

        $queue = new PriorityQueue();
        $queue->insert($foo, 1);
        $queue->insert($foo, 2);

        $queueClone = clone $queue;

        while (! $queue->isEmpty()) {
            $this->assertSame($foo, $queue->top());
            $queue->remove($queue->top());
        }

        $this->assertTrue($queue->isEmpty());
        $this->assertFalse($queueClone->isEmpty());
        $this->assertEquals(2, $queueClone->count());

        while (! $queueClone->isEmpty()) {
            $this->assertSame($foo, $queueClone->top());
            $queueClone->remove($queueClone->top());
        }

        $this->assertTrue($queueClone->isEmpty());
    }

    public function testQueueRevertsToInitialStateWhenEmpty()
    {
        $queue = new PriorityQueue();
        $testQueue = clone $queue; // store the default state

        $testQueue->insert('foo', 1);
        $testQueue->insert('bar', 2);

        $testQueue->remove('foo');
        $testQueue->remove('bar');

        $this->assertEquals($queue, $testQueue);
    }
}
