<?php
namespace MaciejSz\PjFreeze\Process;

use MaciejSz\PjFreeze\PjFreeze;

class SerializeReflectionProperties extends ASerializeWorkUnit
{
    /**
     * @param object $Object
     * @return array
     */
    public function serialize($Object)
    {
        $items = $this->_Status->tryGetFillItems();
        if ( !$items ) {
            $items = [];
        }
        $properties = self::getAllProperties($Object);
        foreach ( $properties as $Property ) {
            if ( $Property->isStatic() ) {
                continue;
            }
            $name = $Property->getName();
            $Property->setAccessible(true);
            $mValue = $Property->getValue($Object);
            if ( $this->_Status->getOnlyScalars() && !is_scalar($mValue) ) {
                $items[$name] = null;
                continue;
            }

            $skip = false;
            if ( array_key_exists($name, $items)) {
                $skip = true;
            }
            if ( $skip && null === $items[$name] && null !== $mValue ) {
                $skip = false;
            }
            if ( $skip ) {
                continue;
            }
            
            $SubStatus = $this->_Status->reset()->appendPath($name);
            $Res = $this->_Serializer->serialize($mValue, $SubStatus);
            $Process = $this->_Status->getProcess();
            $mSerialized = $Process->extractSerialized($Res);
            $items[$name] = $mSerialized;
        }
        return $items;
    }

    /**
     * @param object $Object
     * @return \ReflectionProperty[]
     */
    public static function getAllProperties($Object)
    {
        $Reflection = new \ReflectionObject($Object);
        return self::_doGetProperties($Object, $Reflection);
    }

    /**
     * @param object $Object
     * @param \ReflectionClass $Reflection
     * @return \ReflectionProperty[]
     */
    protected static function _doGetProperties($Object, \ReflectionClass $Reflection)
    {
        $flags = \ReflectionProperty::IS_PUBLIC
            | \ReflectionProperty::IS_PROTECTED
            | \ReflectionProperty::IS_PRIVATE
        ;
        $properties = $Reflection->getProperties($flags);
        $ParentReflection = $Reflection->getParentClass();
        if ( $ParentReflection ) {
            $parent_properties = self::_doGetProperties($Object, $ParentReflection);
            $properties = array_merge($parent_properties, $properties);
        }
        return $properties;
    }
}