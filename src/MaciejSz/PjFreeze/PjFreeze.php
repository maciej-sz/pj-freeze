<?php
namespace MaciejSz\PjFreeze;

use MaciejSz\PjFreeze\Process\PjSerializeProcess;
use MaciejSz\PjFreeze\Process\PjSerializeStatus;
use MaciejSz\PjFreeze\Process\PjSerializer;
use MaciejSz\PjFreeze\Process\PjUnserializeProcess;
use MaciejSz\PjFreeze\Process\PjUnserializer;
use MaciejSz\PjFreeze\Process\SerializationResult;

class PjFreeze
{
    const REFERENCE_PREFIX = "##ref##";

    /**
     * @return PjFreeze
     */
    public static function factory()
    {
        return new self();
    }

    /**
     * @param string $class
     * @return int
     */
    public static function getSerialVersionUid($class)
    {
        $version = 1;
        $name = $class . "::SERIAL_VERSION_UID";
        if ( defined($name) ) {
            $version = constant($name);
        }
        return $version;
    }

    /**
     * @param string $idx
     * @return string
     */
    public static function buildKey($idx)
    {
        return self::REFERENCE_PREFIX . $idx;
    }

    /**
     * @param mixed $mValue
     * @return null|string
     */
    public static function tryExtractReference($mValue)
    {
        if ( !is_string($mValue) ) {
            return null;
        }
        $prefix = self::REFERENCE_PREFIX;
        $prefix_length = strlen($prefix);
        if ( $prefix != substr($mValue, 0, $prefix_length) ) {
            return null;
        }
        return substr($mValue, $prefix_length);
    }

    /**
     * @param mixed $mValue
     * @throws Exc\EDontKnowHowToSerialize
     * @return SerializationResult
     */
    public function serialize($mValue)
    {
        $Process = new PjSerializeProcess();
        $Status = new PjSerializeStatus($Process);
        $Serializer = new PjSerializer();
        return $Serializer->serialize($mValue, $Status);
    }

    /**
     * @param \stdClass $data
     * @return mixed
     */
    public function unserialize(\stdClass $data)
    {
        $Unserializer = new PjUnserializer();
        $Process = PjUnserializeProcess::factory($data);
        return $Unserializer->unserialize($data->root, $Process);
    }

    /**
     * @param string $json_string
     * @return mixed
     */
    public function unserializeJson($json_string)
    {
        $data = json_decode($json_string);
        return $this->unserialize($data);
    }

    /**
     * @param object $Object
     * @throws Exc\EDontKnowHowToSerialize
     * @return SerializationResult
     */
    public function serializeObject($Object)
    {
        $Process = new PjSerializeProcess();
        $Status = new PjSerializeStatus($Process);
        $Serializer = new PjSerializer();
        return $Serializer->serializeObject($Object, $Status);
    }

    /**
     * @param array|\Traversable $mTraversable
     * @throws Exc\EDontKnowHowToSerialize
     * @return SerializationResult
     */
    public function serializeTraversable($mTraversable)
    {
        $Process = new PjSerializeProcess();
        $Status = new PjSerializeStatus($Process);
        $Serializer = new PjSerializer();
        return $Serializer->serializeTraversable($mTraversable, $Status);
    }
}