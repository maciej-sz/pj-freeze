<?php
namespace MaciejSzUt\PjFreeze;

use MaciejSz\PjFreeze\PjFreeze;
use MaciejSzUtFix\PjFreeze\Forum\User;
use PHPUnit\Framework\TestCase;

class BasicUnserializationTest extends TestCase
{
    public function testScalars()
    {
        $Freeze = new PjFreeze();

        $data = [
            1234,
            123.45,
            "foo",
            true,
            false,
            null,
        ];

        foreach ( $data as $item ) {
            $Result = $Freeze->serialize($item);
            $serialized = $Result->jsonSerialize();
            $value = $Freeze->unserialize($serialized);
            $this->assertSame($item, $value);
        }
    }

    public function testListSerialization()
    {
        $data = [
            1234,
            123.45,
            "foo",
            true,
            false,
            null,
        ];

        $Freeze = new PjFreeze();
        $serialized = $Freeze->serialize($data)->jsonSerialize();
        $this->assertSame($data, $Freeze->unserialize($serialized));
    }

    public function testDictSerialization()
    {
        $data = [
            "a" => 1234,
            "b" => 123.45,
            "c" => "foo",
            "d" => true,
            "e" => false,
            "f" => null,
        ];

        $Freeze = new PjFreeze();
        $serialized = $Freeze->serialize($data)->jsonSerialize();
        $this->assertSame($data, $Freeze->unserialize($serialized));
    }

    public function testBasicObjectSerialization()
    {
        $User = new User("John", "john@example.com", "2016-01-01 11:12:32");
        $Freeze = new PjFreeze();


        $serialized = $Freeze->serialize($User)->jsonSerialize();
        /** @var User $UnserializedUser */
        $UnserializedUser = $Freeze->unserialize($serialized);

        $this->assertInstanceOf(User::class, $UnserializedUser);
        $this->assertSame(serialize($User), serialize($UnserializedUser));



        $this->assertEquals("John", $UnserializedUser->name);
        $this->assertEquals("john@example.com", $UnserializedUser->email);
        $this->assertEquals("2016-01-01 11:12:32", $UnserializedUser->joined);
    }

    public function testDirectCircularRecursion()
    {
        $std = new \stdClass();
        $std->std = $std;

        $Freeze = new PjFreeze();
        $Res = $Freeze->serialize($std);
        $serialized = $Res->jsonSerialize();

        $unserialized = $Freeze->unserialize($serialized);

        $this->assertInstanceOf(\stdClass::class, $unserialized);
        $this->assertSame($unserialized, $unserialized->std);
        $this->assertEquals(serialize($std), serialize($unserialized));
    }
}