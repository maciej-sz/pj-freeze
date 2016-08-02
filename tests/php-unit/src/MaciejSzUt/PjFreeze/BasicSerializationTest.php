<?php
namespace MaciejSzUt\PjFreeze;

use MaciejSz\PjFreeze\PjFreeze;
use MaciejSz\PjFreeze\Process\SerializationResult;
use MaciejSzUtFix\PjFreeze\Encapsulation\Sub;
use MaciejSzUtFix\PjFreeze\FixtureHelper;
use MaciejSzUtFix\PjFreeze\Forum\Post;
use MaciejSzUtFix\PjFreeze\Forum\Thread;
use MaciejSzUtFix\PjFreeze\Forum\User;
use PHPUnit\Framework\TestCase;

class BasicSerializationTest extends TestCase
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
            $this->assertInstanceOf(SerializationResult::class, $Result);
            $this->assertSame([], $Result->getObjects());
            $this->assertSame($item, $Result->getRoot());
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
        $this->assertSame($data, $Freeze->serialize($data)->getRoot());
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
        $this->assertSame($data, $Freeze->serialize($data)->getRoot());
    }

    public function testBasicObjectSerialization()
    {
        $User = new User("John", "john@example.com", "2016-01-01 11:12:32");
        $Freeze = new PjFreeze();

        $Helper = FixtureHelper::factory("forum");
        $Res = $Freeze->serialize($User);
        $this->assertEquals(
            $Helper->getContents(__FUNCTION__),
            $Helper->encodeJson($Res)
        );
        $root = $Res->getRoot();
        $this->assertEquals("John", $root->name);
        $this->assertEquals("john@example.com", $root->email);
        $this->assertEquals("2016-01-01 11:12:32", $root->joined);
    }

    public function testDirectCircularRecursion()
    {
        $std = new \stdClass();
        $std->std = $std;

        $Freeze = new PjFreeze();
        $Res = $Freeze->serialize($std);

        $Helper = FixtureHelper::factory("misc");
        $this->assertEquals(
            $Helper->getContents(__FUNCTION__),
            $Helper->encodeJson($Res)
        );
    }

    public function testDirectCircularRecursionGreedy()
    {
        $std = new \stdClass();
        $std->std = $std;

        $Res = PjFreeze::greedy()->serialize($std);

        $Helper = FixtureHelper::factory("misc");
        $this->assertEquals(
            $Helper->getContents(__FUNCTION__),
            $Helper->encodeJson($Res)
        );
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

        $Helper = FixtureHelper::factory("forum");
        $this->assertEquals(
            $Helper->getContents(__FUNCTION__),
            FixtureHelper::encodeJson($Res)
        );
    }

    public function testLevel1ArrayRecursionGreedy()
    {
        $Thread = new Thread("Thread title", "Thread contents");
        $Thread->posts[] = new Post("Post #1", "foo");
        $Thread->posts[] = new Post("Post #2", "bar");
        $Thread->posts[0]->Thread = $Thread;
        $Thread->posts[1]->Thread = $Thread;

        $Res = PjFreeze::greedy()->serialize($Thread);

        $Helper = FixtureHelper::factory("forum");
        $this->assertEquals(
            $Helper->getContents(__FUNCTION__),
            FixtureHelper::encodeJson($Res)
        );

        $this->assertEquals("foo", $Res->getRoot()->posts[0]->contents);
        $this->assertEquals("bar", $Res->getRoot()->posts[1]->contents);
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

        $Freeze = PjFreeze::factory();
        $Helper = FixtureHelper::factory("forum");

        $Res01 = $Freeze->serialize($Thread);
        $this->assertEquals(
            $Helper->getContents(__FUNCTION__ . "_01"),
            FixtureHelper::encodeJson($Res01)
        );
        $Res01a = $Freeze->serialize($Thread->posts[0]);
        $this->assertEquals(
            $Helper->getContents(__FUNCTION__ . "_01a"),
            FixtureHelper::encodeJson($Res01a)
        );

        $Thread->Author = $Kelly;
        $Thread->posts[0]->Author = $John;
        $Thread->posts[1]->Author = $Kelly;
        $Res02 = $Freeze->serialize($Thread);
        $this->assertEquals(
            $Helper->getContents(__FUNCTION__ . "_02"),
            FixtureHelper::encodeJson($Res02)
        );

        $John->entries[] = $Thread->posts[0];
        $Kelly->entries[] = $Thread;
        $Kelly->entries[] = $Thread->posts[1];
        $Res03 = $Freeze->serialize($Thread);
        $this->assertEquals(
            $Helper->getContents(__FUNCTION__ . "_03"),
            FixtureHelper::encodeJson($Res03)
        );
        $Res03a = $Freeze->serialize($Kelly);
        $this->assertEquals(
            $Helper->getContents(__FUNCTION__ . "_03a"),
            FixtureHelper::encodeJson($Res03a)
        );
    }

    public function testSerializeEncapsulatedProperties()
    {
        $Sub = new Sub();

        $Res = PjFreeze::factory()->serialize($Sub);
        echo FixtureHelper::encodeJson($Res);

        $this->markTestIncomplete();
    }

    public function testDeepCircularRecursionGreedy()
    {

        $this->markTestIncomplete();
    }

    public function testSerializeTraversableObject()
    {
        $Object = new \ArrayObject(["foo", "bar"]);

        $this->markTestIncomplete();
    }
}