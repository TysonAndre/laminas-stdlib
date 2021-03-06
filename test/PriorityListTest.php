<?php

/**
 * @see       https://github.com/laminas/laminas-stdlib for the canonical source repository
 * @copyright https://github.com/laminas/laminas-stdlib/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-stdlib/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Stdlib;

use Laminas\Stdlib\PriorityList;
use PHPUnit\Framework\TestCase;

class PriorityListTest extends TestCase
{
    /**
     * @var PriorityList
     */
    protected $list;

    public function setUp()
    {
        $this->list = new PriorityList();
    }

    public function testInsert()
    {
        $this->list->insert('foo', new \stdClass(), 0);

        $this->assertEquals(1, count($this->list));

        foreach ($this->list as $key => $value) {
            $this->assertEquals('foo', $key);
        }
    }

    public function testInsertDuplicates()
    {
        $this->list->insert('foo', new \stdClass());
        $this->list->insert('bar', new \stdClass());

        $this->assertEquals(2, count($this->list));

        $this->list->insert('foo', new \stdClass());
        $this->list->insert('foo', new \stdClass());
        $this->list->insert('bar', new \stdClass());

        $this->assertEquals(2, count($this->list));

        $this->list->remove('foo');

        $this->assertEquals(1, count($this->list));
    }

    public function testRemove()
    {
        $this->list->insert('foo', new \stdClass(), 0);
        $this->list->insert('bar', new \stdClass(), 0);

        $this->assertEquals(2, count($this->list));

        $this->list->remove('foo');

        $this->assertEquals(1, count($this->list));
    }

    public function testRemovingNonExistentRouteDoesNotYieldError()
    {
        $this->list->remove('foo');

        $this->assertEmpty($this->list);
    }

    public function testClear()
    {
        $this->list->insert('foo', new \stdClass(), 0);
        $this->list->insert('bar', new \stdClass(), 0);

        $this->assertEquals(2, count($this->list));

        $this->list->clear();

        $this->assertEquals(0, count($this->list));
        $this->assertSame(false, $this->list->current());
    }

    public function testGet()
    {
        $route = new \stdClass();

        $this->list->insert('foo', $route, 0);

        $this->assertEquals($route, $this->list->get('foo'));
        $this->assertNull($this->list->get('bar'));
    }

    public function testLIFOOnly()
    {
        $this->list->insert('foo', new \stdClass());
        $this->list->insert('bar', new \stdClass());
        $this->list->insert('baz', new \stdClass());
        $this->list->insert('foobar', new \stdClass());
        $this->list->insert('barbaz', new \stdClass());

        $orders = array_keys(iterator_to_array($this->list));

        $this->assertEquals(['barbaz', 'foobar', 'baz', 'bar', 'foo'], $orders);
    }

    public function testPriorityOnly()
    {
        $this->list->insert('foo', new \stdClass(), 1);
        $this->list->insert('bar', new \stdClass(), 0);
        $this->list->insert('baz', new \stdClass(), 2);

        $orders = array_keys(iterator_to_array($this->list));

        $this->assertEquals(['baz', 'foo', 'bar'], $orders);
    }

    public function testLIFOWithPriority()
    {
        $this->list->insert('foo', new \stdClass(), 0);
        $this->list->insert('bar', new \stdClass(), 0);
        $this->list->insert('baz', new \stdClass(), 1);

        $orders = array_keys(iterator_to_array($this->list));

        $this->assertEquals(['baz', 'bar', 'foo'], $orders);
    }

    public function testFIFOWithPriority()
    {
        $this->list->isLIFO(false);
        $this->list->insert('foo', new \stdClass(), 0);
        $this->list->insert('bar', new \stdClass(), 0);
        $this->list->insert('baz', new \stdClass(), 1);

        $orders = array_keys(iterator_to_array($this->list));

        $this->assertEquals(['baz', 'foo', 'bar'], $orders);
    }

    public function testFIFOOnly()
    {
        $this->list->isLIFO(false);
        $this->list->insert('foo', new \stdClass());
        $this->list->insert('bar', new \stdClass());
        $this->list->insert('baz', new \stdClass());
        $this->list->insert('foobar', new \stdClass());
        $this->list->insert('barbaz', new \stdClass());

        $orders = array_keys(iterator_to_array($this->list));

        $this->assertEquals(['foo', 'bar', 'baz', 'foobar', 'barbaz'], $orders);
    }

    public function testPriorityWithNegativesAndNull()
    {
        $this->list->insert('foo', new \stdClass(), null);
        $this->list->insert('bar', new \stdClass(), 1);
        $this->list->insert('baz', new \stdClass(), -1);

        $orders = array_keys(iterator_to_array($this->list));

        $this->assertEquals(['bar', 'foo', 'baz'], $orders);
    }

    public function testCurrent()
    {
        $this->list->insert('foo', 'foo_value', null);
        $this->list->insert('bar', 'bar_value', 1);
        $this->list->insert('baz', 'baz_value', -1);

        $this->assertEquals('bar', $this->list->key());
        $this->assertEquals('bar_value', $this->list->current());
    }

    public function testIterator()
    {
        $this->list->insert('foo', 'foo_value');
        $iterator = $this->list->getIterator();
        $this->assertEquals($iterator, $this->list);

        $this->list->insert('bar', 'bar_value');
        $this->assertNotEquals($iterator, $this->list);
    }

    public function testToArray()
    {
        $this->list->insert('foo', 'foo_value', null);
        $this->list->insert('bar', 'bar_value', 1);
        $this->list->insert('baz', 'baz_value', -1);

        $this->assertEquals(
            [
                'bar' => 'bar_value',
                'foo' => 'foo_value',
                'baz' => 'baz_value'
            ],
            $this->list->toArray()
        );

        $this->assertEquals(
            [
                'bar' => ['data' => 'bar_value', 'priority' => 1, 'serial' => 1],
                'foo' => ['data' => 'foo_value', 'priority' => 0, 'serial' => 0],
                'baz' => ['data' => 'baz_value', 'priority' => -1, 'serial' => 2],
            ],
            $this->list->toArray(PriorityList::EXTR_BOTH)
        );
    }

    /**
     * @group 6768
     * @group 6773
     */
    public function testBooleanValuesAreValid()
    {
        $this->list->insert('null', null, null);
        $this->list->insert('false', false, null);
        $this->list->insert('string', 'test', 1);
        $this->list->insert('true', true, -1);

        $orders1 = [];
        $orders2 = [];

        foreach ($this->list as $key => $value) {
            $orders1[$this->list->key()] = $this->list->current();
            $orders2[$key] = $value;
        }
        $this->assertEquals($orders1, $orders2);
        $this->assertEquals(
            [
                'null'   => null,
                'false'  => false,
                'string' => 'test',
                'true'   => true,
            ],
            $orders2
        );
    }
}
