<?php
/**
 * Created by PhpStorm.
 * User: k1p
 * Date: 4/16/19
 * Time: 12:39 PM
 */

namespace tests\Library\HM\Collection;


use PHPUnit\Framework\TestCase;

class PrimitiveTest extends TestCase
{
    public function testCanChangeEventDispatcher()
    {
        $primitiveCollection = new \HM_Collection_Primitive();
        $testEventDispatcher = new \sfEventDispatcher();

        $primitiveCollection->setEventDispatcher($testEventDispatcher);
        $this->assertSame($primitiveCollection->getEventDispatcher(), $testEventDispatcher);
    }

    public function testCanAddItemWithNameToTheCollection()
    {
        $primitiveCollection = new \HM_Collection_Primitive();

        $firstItemObject = new \stdClass;
        $firstItemName = 'firstItem';
        $primitiveCollection->add($firstItemObject, $firstItemName);

        $this->assertSame($firstItemObject, $primitiveCollection[$firstItemName]);
    }

    public function testCanAddItemWithoutNameToTheCollection()
    {
        $primitiveCollection = new \HM_Collection_Primitive();

        $firstItemObject = new \stdClass;
        $primitiveCollection->add($firstItemObject);

        $secondItemObject = new \stdClass;
        $primitiveCollection->add($secondItemObject);

        $this->assertSame($secondItemObject, $primitiveCollection[1]);
    }

    public function testCounterIsWorkingProperly()
    {
        $primitiveCollection = new \HM_Collection_Primitive();

        $primitiveCollection->add(new \stdClass);
        $primitiveCollection->add(new \stdClass);
        $primitiveCollection->add(new \stdClass);
        $primitiveCollection->add(new \stdClass);

        $primitiveCollection->offsetUnset(0);

        $this->assertEquals(3, $primitiveCollection->count());
    }

    public function testIterateCollection()
    {
        $primitiveCollection = new \HM_Collection_Primitive();
        $insertItems = [
            'first' => new \StdClass,
            'second' => new \StdClass,
            'third' => new \StdClass,
            'fourth' => new \StdClass,
        ];

        foreach ($insertItems as $insertName => $insertValue) {
            $primitiveCollection->add($insertValue, $insertName);
        }

        $this->assertSame($insertItems['first'], $primitiveCollection->current());
        $this->assertSame('first', $primitiveCollection->key());
        $this->assertEquals(true, $primitiveCollection->valid());
        $primitiveCollection->next();
        $this->assertSame($insertItems['second'], $primitiveCollection->current());
        $this->assertSame('second', $primitiveCollection->key());
        $this->assertEquals(true, $primitiveCollection->valid());
        $primitiveCollection->next();
        $this->assertSame($insertItems['third'], $primitiveCollection->current());
        $this->assertSame('third', $primitiveCollection->key());
        $this->assertEquals(true, $primitiveCollection->valid());
        $primitiveCollection->next();
        $this->assertSame($insertItems['fourth'], $primitiveCollection->current());
        $this->assertSame('fourth', $primitiveCollection->key());
        $this->assertEquals(true, $primitiveCollection->valid());

        $primitiveCollection->next();
        $this->assertEquals(false, $primitiveCollection->valid());

        $primitiveCollection->rewind();
        $this->assertSame($insertItems['first'], $primitiveCollection->current());
        $this->assertSame('first', $primitiveCollection->key());
        $this->assertEquals(true, $primitiveCollection->valid());
    }

    public function testArrayAccessCollection()
    {
        $primitiveCollection = new \HM_Collection_Primitive();
        $insertItems = [
            'first' => new \StdClass,
            'second' => new \StdClass,
            'third' => new \StdClass,
            'fourth' => new \StdClass,
        ];

        foreach ($insertItems as $insertName => $insertValue) {
            $primitiveCollection->add($insertValue, $insertName);
            $primitiveCollection->offsetSet($insertName, $insertValue);

            $this->assertSame(true, $primitiveCollection->offsetExists($insertName));
            $this->assertSame($insertValue, $primitiveCollection->offsetGet($insertName));

            $primitiveCollection->offsetUnset($insertName);
            $this->assertSame(false, $primitiveCollection->offsetExists($insertName));
        }
    }
}