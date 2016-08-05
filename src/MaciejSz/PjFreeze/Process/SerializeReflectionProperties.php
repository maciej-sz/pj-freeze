<?php
namespace MaciejSz\PjFreeze\Process;

class SerializeReflectionProperties
{
    /**
     * @var PjSerializer
     */
    private $_Serializer;

    /**
     * @var PjSerializeStatus
     */
    private $_Status;

    /**
     * @param PjSerializer $Serializer
     * @param PjSerializeStatus $Status
     */
    public function __construct(PjSerializer $Serializer, PjSerializeStatus $Status)
    {
        $this->_Serializer = $Serializer;
        $this->_Status = $Status;
    }

    /**
     * @param object $Object
     * @return array
     */
    public function serializeScalars($Object)
    {
        return $this->_doSerialize(
            $Object,
            function($mValue){
                return !is_object($mValue);
            }
        );
    }

    /**
     * @param object $Object
     * @return array
     */
    public function serializeObjects($Object)
    {
        return $this->_doSerialize(
            $Object,
            function($mValue){
                return is_object($mValue);
            }
        );
    }

    /**
     * @param object $Object
     * @param callable $cPredicate
     * @return array
     */
    protected function _doSerialize($Object, callable $cPredicate = null)
    {
        if ( null === $cPredicate ) {
            $cPredicate = function(){ return true; };
        }

        $items = [];
        $properties = self::getAllProperties($Object);
        foreach ( $properties as $Property ) {
            if ( $Property->isStatic() ) {
                continue;
            }
            $name = $Property->getName();
            $Property->setAccessible(true);
            $mValue = $Property->getValue($Object);
            $idx = $this->_Status->getProcess()->tryGetObjectReference($Object);
            $SubStatus = $this->_Status->appendPathProperty($name, $idx);
            if ( !$cPredicate($mValue) ) {
                continue;
            }
            $Res = $this->_Serializer->serialize($mValue, $SubStatus);
            $Process = $this->_Status->getProcess();
            $items[$name] = $Process->extractSerialized($Res);
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