<?php
namespace MaciejSzUt\PjFreeze;

use MaciejSz\PjFreeze\PjFreeze;
use PHPUnit\Framework\TestCase;

class EncodingTest extends TestCase
{
    public function testEncodeRecursion()
    {
        $Freeze = new PjFreeze();

        $data = new \stdClass();
        $data->recursion = $data; // circular reference

        $serializedObj = $Freeze->serialize($data)->jsonSerialize();
        $jp_freeze_encoded = json_encode($serializedObj, JSON_PRETTY_PRINT);

        $unserializedObj = json_decode($jp_freeze_encoded);
        $unserialized = $Freeze->unserialize($unserializedObj);
        $this->assertSame($unserialized, $unserialized->recursion);
    }
}