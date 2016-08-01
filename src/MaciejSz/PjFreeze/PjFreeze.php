<?php
namespace MaciejSz\PjFreeze;

use MaciejSz\PjFreeze\Process\PjFreezeProcess;

class PjFreeze
{
    const REFERENCE_PREFIX = "##ref##";

    /**
     * @param mixed $mValue
     * @throws Exc\EDontKnowHowToSerialize
     * @return Process\SerializationResult
     */
    public function serialize($mValue)
    {
        $Process = new PjFreezeProcess();
        $root = $this->_doSerialize($mValue, $Process);
        return $Process->makeResult($root);
    }

    /**
     * @param object $Object
     * @throws Exc\EDontKnowHowToSerialize
     * @return Process\SerializationResult
     */
    public function serializeObject($Object)
    {
        $Process = new PjFreezeProcess();
        $item = $this->_doSerializeObject($Object, $Process);
        return $Process->makeResult($item);
    }

    /**
     * @param array|\Traversable $mTraversable
     * @throws Exc\EDontKnowHowToSerialize
     * @return Process\SerializationResult
     */
    public function serializeTraversable($mTraversable)
    {
        $Process = new PjFreezeProcess();
        $root = $this->_doSerializeTraversable($mTraversable, $Process);
        return $Process->makeResult($root);
    }

    /**
     * @param mixed $mValue
     * @param PjFreezeProcess $Process
     * @throws Exc\EDontKnowHowToSerialize
     * @return Process\SerializationResult
     */
    protected function _doSerialize($mValue, PjFreezeProcess $Process)
    {
        if ( is_scalar($mValue) ) {
            return $mValue;
        }
        else if ( $mValue instanceof \JsonSerializable ) {
            return $mValue->jsonSerialize();
        }
        else if ( is_object($mValue) ) {
            return $this->_doSerializeObject($mValue, $Process);
        }
        else if ( is_array($mValue) ) {
            return $this->_doSerializeTraversable($mValue, $Process);
        }
        throw Exc\EDontKnowHowToSerialize::factory($mValue);
    }

    /**
     * @param object $Object
     * @param PjFreezeProcess $Process
     * @return \stdClass|string
     */
    protected function _doSerializeObject($Object, PjFreezeProcess $Process)
    {
        if ( $Process->hasObject($Object) ) {
            return self::REFERENCE_PREFIX . $Process->getObjectReference($Object);
        }
        $item = new \stdClass();
        $idx = $Process->putObject($Object);
        $Reflection = new \ReflectionObject($Object);
        $properties = $Reflection->getProperties();
        foreach ( $properties as $Property ) {
            if ( $Property->isStatic() ) {
                continue;
            }
            $name = $Property->getName();
            $Property->setAccessible(true);
            $mValue = $Property->getValue($Object);
            $item->$name = $this->_doSerialize($mValue, $Process);
        }
        $Process->putObjectRepresentation($idx, $item);
        return $item;
    }

    /**
     * @param array|\Traversable $mTraversable
     * @param PjFreezeProcess $Process
     * @return array
     */
    protected function _doSerializeTraversable($mTraversable, PjFreezeProcess $Process)
    {
        $items = [];
        foreach ( $mTraversable as $key => $mValue ) {
            $items[$key] = $this->_doSerialize($mValue, $Process);
        }
        return $items;
    }
}