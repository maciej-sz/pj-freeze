<?php
namespace MaciejSz\PjFreeze\Process;

use MaciejSz\PjFreeze\Exc\EDontKnowHowToUnserialize;
use MaciejSz\PjFreeze\PjFreeze;

class PjUnserializer
{
    public function unserialize($mValue, \stdClass $data)
    {
        if ( PjFreeze::tryExtractReference($mValue) ) {
            return $this->_unserializeObject($mValue, $data);
        }
    }

    /**
     * @param string $reference
     * @param \stdClass $data
     * @return object
     */
    protected function _unserializeObject($reference, \stdClass $data)
    {
        $idx = PjFreeze::tryExtractReference($reference);
        $object = $data->objects->$idx;
        $class = $data->meta->classes->$idx;
        $Instance = null;
        if ( "stdclass" === strtolower(ltrim($class, "\\")) ) {
            $Instance = $this->_unserializeStdObject($reference, $data);
        }
        else {
            $Instance = $this->_unserializeReflectableObject($reference, $data);
        }
        return $Instance;
    }

    /**
     * @param string $reference
     * @param \stdClass $data
     * @return \stdClass
     */
    protected function _unserializeStdObject($reference, \stdClass $data)
    {
        $idx = PjFreeze::tryExtractReference($reference);
        $object = $data->objects->$idx;
        $instance = new \stdClass();
        foreach ( $object as $key => $mSubValue ) {
            $instance->$key = $this->unserialize($mSubValue, $data);
        }
        return $instance;
    }

    /**
     * @param $reference
     * @param \stdClass $data
     * @return object
     * @throws EDontKnowHowToUnserialize
     */
    protected function _unserializeReflectableObject($reference, \stdClass $data)
    {
        $idx = PjFreeze::tryExtractReference($reference);
        $object = $data->objects->$idx;
        $class = $data->meta->classes->$idx;

        $Reflection = new \ReflectionClass($class);
        $Instance = $Reflection->newInstanceWithoutConstructor();

        if ( is_object($object) ) {
            $this->_fillReflectableObject($object, $Instance, $data);
        }
        else if ( is_array($object) ) {
            $this->_fillTraversableObject($object, $Instance, $data);
        }
        else {
            throw new EDontKnowHowToUnserialize($reference);
        }

        return $Instance;
    }

    /**
     * @param $object
     * @param $Instance
     * @param \stdClass $data
     * @return void
     */
    protected function _fillReflectableObject(
        $object,
        $Instance,
        \stdClass $data
    )
    {
        $Reflection = new \ReflectionObject($Instance);
        foreach ( $object as $key => $mSubValue ) {
            $mUnserialzedSubValue = $this->unserialize($mSubValue, $data);
            $Property = $Reflection->getProperty($key);
            $Property->setAccessible(true);
            $Property->setValue($Instance, $mUnserialzedSubValue);
        }
    }

    /**
     * @param $object
     * @param $Instance
     * @param \stdClass $data
     */
    protected function _fillTraversableObject(
        $object,
        $Instance,
        \stdClass $data
    )
    {
        foreach ( $object as $key => $mSubValue ) {
            $Instance[$key] = $this->unserialize($mSubValue, $data);
        }
    }

    protected function _unserializeTraversable(array $items, \stdClass $data)
    {

    }
}