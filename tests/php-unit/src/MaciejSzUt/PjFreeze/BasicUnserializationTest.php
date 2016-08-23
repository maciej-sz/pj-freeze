<?php
namespace MaciejSzUt\PjFreeze;

use MaciejSz\PjFreeze\PjFreeze;
use MaciejSzUtFix\PjFreeze\Encapsulation\Sub;
use MaciejSzUtFix\PjFreeze\Forum\Post;
use MaciejSzUtFix\PjFreeze\Forum\Thread;
use MaciejSzUtFix\PjFreeze\Forum\User;
use MaciejSzUtFix\PjFreeze\Misc\Container;
use MaciejSzUtFix\PjFreeze\Misc\WithStatic;
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

    public function testLevel1ArrayRecursion()
    {
        $Thread = new Thread("Thread title", "Thread contents");
        $Thread->posts[] = new Post("Post #1", "foo");
        $Thread->posts[] = new Post("Post #2", "bar");
        $Thread->posts[0]->Thread = $Thread;
        $Thread->posts[1]->Thread = $Thread;

        $Freeze = new PjFreeze();
        $Res = $Freeze->serialize($Thread);
        $serialized = $Res->jsonSerialize();
        $UnserializedThread = $Freeze->unserialize($serialized);
        $this->assertEquals(
            serialize($Thread),
            serialize($UnserializedThread)
        );

        $this->assertNotSame($Thread, $UnserializedThread);
    }

    public function testDeepCircularRecursion()
    {
        $Thread = new Thread("Thread title", "Thread contents");
        $Thread->posts[] = new Post("Post #1", "foo");
        $Thread->posts[] = new Post("Post #2", "bar");
        $Thread->posts[0]->Thread = $Thread;
        $Thread->posts[1]->Thread = $Thread;

        $John = new User("John");
        $Kelly = new User("Kelly");

        $Thread->posts[0]->Author = $John;
        $Thread->posts[1]->Author = $Kelly;

        $Thread->Author = $Kelly;

        $John->entries[] = $Thread->posts[0];
        $Kelly->entries[] = $Thread;
        $Kelly->entries[] = $Thread->posts[1];

        $Freeze = PjFreeze::factory();

        $threadStd = $Freeze->serialize($Thread)->jsonSerialize();
        $ThreadUnserialized = $Freeze->unserialize($threadStd);

        $this->assertEquals(
            serialize($Thread),
            serialize($ThreadUnserialized)
        );

        $post1Std = $Freeze->serialize($Thread->posts[0])->jsonSerialize();
        $Post1Unserialized = $Freeze->unserialize($post1Std);

        $this->assertEquals(
            serialize($Thread->posts[0]),
            serialize($Post1Unserialized)
        );

        $post2Std = $Freeze->serialize($Thread->posts[1])->jsonSerialize();
        $Post2Unserialized = $Freeze->unserialize($post2Std);

        $this->assertEquals(
            serialize($Thread->posts[1]),
            serialize($Post2Unserialized)
        );

        $kellyStd = $Freeze->serialize($Kelly)->jsonSerialize();
        $KellyUnserialized = $Freeze->unserialize($kellyStd);

        $this->assertEquals(
            serialize($Kelly),
            serialize($KellyUnserialized)
        );

        $johnStd = $Freeze->serialize($John)->jsonSerialize();
        $JohnUnserialized = $Freeze->unserialize($johnStd);

        $this->assertEquals(
            serialize($John),
            serialize($JohnUnserialized)
        );
    }

    public function testRecursiveContainer()
    {
        $Container = new Container();
        $Freeze = PjFreeze::factory();
        $containserStd = $Freeze->serialize($Container)->jsonSerialize();
        $ContainerUnserialized = $Freeze->unserialize($containserStd);

        $this->assertEquals(
            serialize($Container),
            serialize($ContainerUnserialized)
        );

        $this->assertNotSame($Container, $ContainerUnserialized);
    }

    public function testSerializeEncapsulatedProperties()
    {
        $Sub = new Sub();

        $Freeze = PjFreeze::factory();
        $subStd = $Freeze->serialize($Sub)->jsonSerialize();
        $SubUnserialized = $Freeze->unserialize($subStd);

        $this->assertEquals(
            serialize($Sub),
            serialize($SubUnserialized)
        );
    }

    public function testSerializeStatic()
    {
        $Object = new WithStatic();
        $Freeze = PjFreeze::factory();
        $objectStd = $Freeze->serialize($Object)->jsonSerialize();
        $ObjectUnserialized = $Freeze->unserialize($objectStd);

        $this->assertEquals(
            serialize($Object),
            serialize($ObjectUnserialized)
        );
    }

    public function testSerializeArray()
    {
        $arr = [123, "foo", new \stdClass()];
        $Freeze = PjFreeze::factory();
        $arrStd = $Freeze->serialize($arr)->jsonSerialize();
        $arr_unserialized = $Freeze->unserialize($arrStd);

        $this->assertEquals(
            serialize($arr),
            serialize($arr_unserialized)
        );
    }

    public function testSerializeTraversableObject()
    {
        $Object = new \ArrayObject(["foo", "bar"]);

        $Freeze = PjFreeze::factory();
        $objectStd = $Freeze->serializeTraversable($Object)->jsonSerialize();
        $ObjectUnserialized = $Freeze->unserialize($objectStd);

        $this->assertEquals(
            serialize($Object),
            serialize($ObjectUnserialized)
        );
    }

    public function testNestedArrays()
    {
        $arr = [
            123,
            [
                "foo",
                "bar",
            ],
        ];

        $Freeze = PjFreeze::factory();
        $arrStd = $Freeze->serializeTraversable($arr)->jsonSerialize();
        $arr_unserialized = $Freeze->unserialize($arrStd);

        $this->assertEquals(
            serialize($arr),
            serialize($arr_unserialized)
        );
    }

    public function testNestedArraysWithObjects()
    {
        $Freeze = PjFreeze::factory();


        $data1 = new \stdClass();
        $data1->data = "foo";
        $Post = new Post("post");

        $arr1 = [
            $data1,
            $Post,
            [
                $data1,
                $Post,
            ],
        ];

        $arr1Std = $Freeze->serializeTraversable($arr1)->jsonSerialize();
        $arr1_unserialized = $Freeze->unserialize($arr1Std);

        $this->assertEquals(
            serialize($arr1),
            serialize($arr1_unserialized)
        );


        $arr2 = [
            123,
            [
                $data1,
                $data1,
                $Post,
                $Post,
            ]
        ];

        $arr2Std = $Freeze->serializeTraversable($arr2)->jsonSerialize();
        $arr2_unserialized = $Freeze->unserialize($arr2Std);

        $this->assertEquals(
            serialize($arr2),
            serialize($arr2_unserialized)
        );
    }
}