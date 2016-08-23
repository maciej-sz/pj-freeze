<?php
namespace MaciejSz\PjFreeze\Process;

class UnserializerReflectionPropertiesFiller extends AUnserializeWorkUnit
{
    /**
     * @param \stdClass $object
     * @param object $Instance
     * @param \ReflectionClass|null $Reflection
     */
    public function fill(\stdClass $object, $Instance, \ReflectionClass $Reflection = null)
    {
        if ( null === $Reflection ) {
            $Reflection = new \ReflectionObject($Instance);
        }
        foreach ( $object as $key => $mValue ) {
            if ( !$Reflection->hasProperty($key) ) {
                continue;
            }
            $Property = $Reflection->getProperty($key);
            $Property->setAccessible(true);
            $mUnserializedValue = $this->_Unserializer->unserialize(
                $mValue,
                $this->_Process
            );
            $Property->setValue($Instance, $mUnserializedValue);
        }
        $ParentReflection = $Reflection->getParentClass();
        if ( $ParentReflection ) {
            $this->fill($object, $Instance, $ParentReflection);
        }
    }
}