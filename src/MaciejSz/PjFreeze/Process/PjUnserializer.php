<?php
namespace MaciejSz\PjFreeze\Process;

use MaciejSz\PjFreeze\Exc\EDontKnowHowToUnserialize;
use MaciejSz\PjFreeze\PjFreeze;

class PjUnserializer
{
    /**
     * @param \stdClass $serialized
     * @return mixed
     */
    public function unserialize(\stdClass $serialized)
    {
        $Process = new PjUnserializeProcess($serialized);
        return $this->_unserialize($serialized->root, $Process);
    }

    /**
     * @param mixed $mValue
     * @param PjUnserializeProcess $Process
     * @return mixed
     */
    protected function _unserialize($mValue, PjUnserializeProcess $Process)
    {
        if ( null === $mValue ) {
            return null;
        }
        else if ( PjFreeze::tryExtractReference($mValue) ) {
            return $this->_unserializeObject($mValue, $Process);
        }
        else if ( is_scalar($mValue) || is_array($mValue) ) {
            return $mValue;
        }
        else {
            throw EDontKnowHowToUnserialize::factory($mValue);
        }
    }

    /**
     * @param string $reference
     * @param PjUnserializeProcess $Process
     * @return object
     */
    protected function _unserializeObject($reference, PjUnserializeProcess $Process)
    {
        $idx = PjFreeze::tryExtractReference($reference);
        $serialized = $Process->getSerialized();
        $class = $serialized->meta->classes[$idx];
        $Instance = $Process->tryGetObject($idx);
        if ( null !== $Instance ) {
            return $Instance;
        }
        if ( "stdclass" === strtolower(ltrim($class, "\\")) ) {
            $Instance = $this->_unserializeStdObject($idx, $Process);
        }
        else {
            $Instance = $this->_unserializeReflectableObject($idx, $Process);
        }
        return $Instance;
    }

    /**
     * @param string $idx
     * @param PjUnserializeProcess $Process
     * @return \stdClass
     */
    protected function _unserializeStdObject($idx, PjUnserializeProcess $Process)
    {
        $object = $Process->getSerialized()->objects[$idx];
        $instance = new \stdClass();
        $Process->putObject($idx, $instance);
        foreach ( $object as $key => $mSubValue ) {
            $instance->$key = $this->_unserialize($mSubValue, $Process);
        }
        return $instance;
    }

    /**
     * @param $idx
     * @param PjUnserializeProcess $Process
     * @return object
     */
    protected function _unserializeReflectableObject(
        $idx,
        PjUnserializeProcess $Process
    )
    {
        $serialized = $Process->getSerialized();
        $object = $serialized->objects[$idx];
        $class = $serialized->meta->classes[$idx];

        $Reflection = new \ReflectionClass($class);
        $Instance = $Reflection->newInstanceWithoutConstructor();
        $Process->putObject($idx, $Instance);

        if ( is_object($object) ) {
            $this->_fillReflectableObject($object, $Instance, $Process);
        }
        else if ( is_array($object) ) {
            $this->_fillTraversableObject($object, $Instance, $Process);
        }
        else {
            throw EDontKnowHowToUnserialize::factoryReference($idx);
        }

        return $Instance;
    }

    /**
     * @param $object
     * @param $Instance
     * @param PjUnserializeProcess $Process
     */
    protected function _fillReflectableObject(
        $object,
        $Instance,
        PjUnserializeProcess $Process
    )
    {
        $Reflection = new \ReflectionObject($Instance);
        foreach ( $object as $key => $mSubValue ) {
            $mUnserialzedSubValue = $this->_unserialize($mSubValue, $Process);
            $Property = $Reflection->getProperty($key);
            $Property->setAccessible(true);
            $Property->setValue($Instance, $mUnserialzedSubValue);
        }
    }

    /**
     * @param $object
     * @param $Instance
     * @param PjUnserializeProcess $Process
     */
    protected function _fillTraversableObject(
        $object,
        $Instance,
        PjUnserializeProcess $Process
    )
    {
        foreach ( $object as $key => $mSubValue ) {
            $Instance[$key] = $this->_unserialize($mSubValue, $Process);
        }
    }
}