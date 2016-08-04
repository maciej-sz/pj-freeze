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
        if ( $Process->hasObject($Object) ) {
            $idx = $Process->tryGetObjectReference($Object);
            $key = PjFreeze::buildKey($idx);
            return $Process->makeResult($key, $key);
        }
        $idx = $Process->putObject($Object);
        $item = (object)$this->_serializeReflectionProperties(
            new \ReflectionObject($Object),
            $Object,
            $Status
        );
        $Process->putObjectRepresentation($idx, $item);
        return $Process->makeResult($Object, $item);
    }

    /**
     * @param array|\Traversable $mTraversable
     * @param PjSerializeStatus $Status
     * @return array
     */
    public function serializeTraversable($mTraversable, PjSerializeStatus $Status)
    {
        $Process = $Status->getProcess();
        $idx = null;
        if ( is_object($mTraversable) ) {
            if ( $Process->hasObject($mTraversable) ) {
                $idx = $Process->tryGetObjectReference($mTraversable);
                $key = PjFreeze::buildKey($idx);
                return $Process->makeResult($key, $key);
            }
            else {
                $idx = $Process->putObject($mTraversable);
            }
        }
        $items = [];
        foreach ( $mTraversable as $sub_key => $mValue ) {
            $sub_idx = $Process->tryGetObjectReference($mValue);
            if ( $sub_idx ) {
                $items[$sub_key] = PjFreeze::buildKey($sub_idx);
            }
            else {
                $SubStatus = $Status->appendPathTraversable($sub_key, $sub_idx);
                $Res = $this->serialize($mValue, $SubStatus);
                $items[$sub_key] = $Process->extractSerialized($Res);
            }
        }
        if ( null !== $idx ) {
            $Process->putObjectRepresentation($idx, $items);
        }
        return $Process->makeResult($mTraversable, $items);
    }

    /**
     * @param \ReflectionClass $Reflection
     * @param $Object
     * @param PjSerializeStatus $Status
     * @return array
     */
    protected function _serializeReflectionProperties(
        \ReflectionClass $Reflection,
        $Object,
        PjSerializeStatus $Status
    )
    {
        $items = [];
        $properties = $Reflection->getProperties();
        foreach ( $properties as $Property ) {
            if ( $Property->isStatic() ) {
                continue;
            }
            $name = $Property->getName();
            $Property->setAccessible(true);
            $mValue = $Property->getValue($Object);
            $idx = $Status->getProcess()->tryGetObjectReference($Object);
            $SubStatus = $Status->appendPathProperty($name, $idx);
            $Res = $this->serialize($mValue, $SubStatus);
            $Process = $Status->getProcess();
            $items[$name] = $Process->extractSerialized($Res);
        }
        $ParentReflection = $Reflection->getParentClass();
        if ($ParentReflection) {
            $parent_items = $this->_serializeReflectionProperties(
                $ParentReflection,
                $Object,
                $Status
            );
            $items = array_merge($parent_items, $items);
        }
        return $items;
    }
}