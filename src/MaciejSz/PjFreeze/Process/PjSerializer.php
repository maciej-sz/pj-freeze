<?php
namespace MaciejSz\PjFreeze\Process;

use MaciejSz\PjFreeze\Exc\EDontKnowHowToSerialize;
use MaciejSz\PjFreeze\PjFreeze;

class PjSerializer extends AFreezeWorkUnit
{
    /**
     * @param mixed $mValue
     * @param PjSerializeStatus $Status
     * @throws EDontKnowHowToSerialize
     * @return SerializationResult
     */
    public function serialize($mValue, PjSerializeStatus $Status)
    {
        if ( null === $mValue || is_scalar($mValue) ) {
            return $Status->getProcess()->makeResult($mValue, $mValue);
        }
        else if ( $mValue instanceof \JsonSerializable ) {
            return $Status->getProcess()->makeResult($mValue, $mValue->jsonSerialize());
        }
        else if ( $mValue instanceof \stdClass ) {
            return $this->serializeTraversable($mValue, $Status);
        }
        else if ( is_object($mValue) ) {
            return $this->serializeObject($mValue, $Status);
        }
        else if ( is_array($mValue) ) {
            return $this->serializeTraversable($mValue, $Status);
        }
        throw EDontKnowHowToSerialize::factory($mValue);
    }

    /**
     * @param object $Object
     * @param PjSerializeStatus $Status
     * @return \stdClass|string
     */
    public function serializeObject($Object, PjSerializeStatus $Status)
    {
        $Process = $Status->getProcess();
        $idx = null;
        if ( $Process->hasObject($Object) ) {
            $idx = $Process->tryGetObjectReference($Object);
            $key = PjFreeze::buildKey($idx);
            if ( !$Status->tryGetFillItems() ) {
                $Res = $Process->makeResult($key, $key);
                $mSerialized = $Process->extractSerialized($Res);
                if ( !PjFreeze::tryExtractReference($mSerialized) ) {
                    $Status->savePathIdx($idx);
                }
                return $Res;
            }
        }
        else {
            $idx = $Process->putObject($Object);
            $Status = $Status->savePathIdx($idx);
        }

        $item = (object)$this->_serializeReflectionProperties($Object, $Status);
        $Process->putObjectRepresentation($idx, $item);
        return $Process->makeResult($Object, $item);
    }

    /**
     * @param array|\Traversable $mTraversable
     * @param PjSerializeStatus $Status
     * @return SerializationResult
     */
    public function serializeTraversable($mTraversable, PjSerializeStatus $Status)
    {
        $Process = $Status->getProcess();
        $idx = null;
        if ( is_object($mTraversable) ) {
            if ( $Process->hasObject($mTraversable) ) {
                $idx = $Process->tryGetObjectReference($mTraversable);
                $Status->savePathIdx($idx);
                $key = PjFreeze::buildKey($idx);
                return $Process->makeResult($key, $key);
            }
            else {
                $idx = $Process->putObject($mTraversable);
                $Status = $Status->savePathIdx($idx);
            }
        }

        $WorkUnit = new SerializeTraversable($this, $Status);
        $items = $WorkUnit->serialize($mTraversable);
//        $scalars = $WorkUnit->serializeScalars($mTraversable);
//        $objects = $WorkUnit->serializeObjects($mTraversable);
//        $all = [];
//        foreach ( $mTraversable as $key => $mValue ) {
//            if ( array_key_exists($key, $scalars) ) {
//                $all[$key] = $scalars[$key];
//            }
//            else {
//                $all[$key] = $objects[$key];
//            }
//        }

        if ( null !== $idx ) {
            $Process->putObjectRepresentation($idx, $items);
        }
        return $Process->makeResult($mTraversable, $items);
    }

    /**
     * @param $Object
     * @param PjSerializeStatus $Status
     * @return array
     */
    protected function _serializeReflectionProperties($Object, PjSerializeStatus $Status)
    {
        $WorkUnit = new SerializeReflectionProperties($this, $Status);
        return $WorkUnit->serialize($Object);
//        $scalars = $WorkUnit->serializeScalars($Object);
//        $objects = $WorkUnit->serializeObjects($Object);
//        $properties = SerializeReflectionProperties::getAllProperties($Object);
//        $all = [];
//        foreach ( $properties as $Property ) {
//            if ( $Property->isStatic() ) {
//                continue;
//            }
//            $name = $Property->getName();
//            if ( array_key_exists($name, $scalars) ) {
//                $all[$name] = $scalars[$name];
//            }
//            else {
//                $all[$name] = $objects[$name];
//            }
//        }
//        return $all;
    }
}