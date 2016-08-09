<?php
namespace MaciejSz\PjFreeze\Process;

use MaciejSz\PjFreeze\PjFreeze;

class SerializeTraversable extends ASerializeWorkUnit
{
//    /**
//     * @var null|SerializeReflectionProperties
//     */
//    private $_SrpUnit;
//
//    /**
//     * @return SerializeReflectionProperties
//     */
//    protected function _getSrpUnit()
//    {
//        if ( null === $this->_SrpUnit ) {
//            $this->_SrpUnit = new SerializeReflectionProperties(
//                $this->_Serializer,
//                $this->_Status
//            );
//        }
//        return $this->_SrpUnit;
//    }

//    /**
//     * @param mixed $mTraversable
//     * @return array
//     */
//    public function serializeScalars($mTraversable)
//    {
//        return $this->_doSerialize($mTraversable, function($mValue){
//            return !is_object($mValue);
//        });
//    }
//
//    /**
//     * @param mixed $mTraversable
//     * @return array
//     */
//    public function serializeObjects($mTraversable)
//    {
//        return $this->_doSerialize($mTraversable, function($mValue){
//            return is_object($mValue);
//        });
//    }

    /**
     * @param mixed $mTraversable
     * @return array
     * @throws \MaciejSz\PjFreeze\Exc\EInvalidVersion
     */
    public function serialize($mTraversable)
    {
        $Status = $this->_Status;
        $Process = $Status->getProcess();
        $items = [];
        /** @var PjSerializeStatus[] $to_fill */
        $to_fill = [];
        foreach ( $mTraversable as $sub_key => $mValue ) {
            $sub_idx = $Process->tryGetObjectReference($mValue);
            if ( $sub_idx ) {
                $ref = PjFreeze::buildKey($sub_idx);
                $items[$sub_key] = $Process->extractSerializedByIdx($sub_idx, $ref);
            }
            else {
                $CommonStatus = $Status
                    ->reset()
                    ->appendPathTraversable($sub_key, $sub_idx)
                ;
                $SubStatus = $CommonStatus->onlyScalars(true);

                $Res = $this->_Serializer->serialize($mValue, $SubStatus);
                $extracted = $Process->extractSerialized($Res);
                if ( $extracted instanceof \stdClass ) {
                    $items[$sub_key] = null;
                    $to_fill[$sub_key] = $CommonStatus->withFillItems((array)$extracted);
                }
                else {
                    $idx = PjFreeze::tryExtractReference($extracted);
                    if ( $idx ) {
                        $items[$sub_key] = null;
                        $representation = $Process->tryGetObjectRepresentation($idx);
                        $to_fill[$sub_key] = $CommonStatus->withFillItems((array)$representation);
                    }
                    else {
                        $items[$sub_key] = $extracted;
                    }
                }
            }
        }
        foreach ( $to_fill as $sub_key => $ToFillStatus ) {
            $mValue = $mTraversable[$sub_key];
            $Res = $this->_Serializer->serialize($mValue, $ToFillStatus);
            $items[$sub_key] = $Process->extractSerialized($Res);
        }
        return $items;
    }
}