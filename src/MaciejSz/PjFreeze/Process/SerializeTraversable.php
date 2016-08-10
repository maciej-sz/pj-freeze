<?php
namespace MaciejSz\PjFreeze\Process;

use MaciejSz\PjFreeze\PjFreeze;

class SerializeTraversable extends ASerializeWorkUnit
{
    /**
     * @param $mTraversable
     * @return array
     */
    public function serialize($mTraversable)
    {
        $Process = $this->_Status->getProcess();
        $items = [];
        foreach ( $mTraversable as $sub_key => $mValue ) {
            $sub_idx = $Process->tryGetObjectReference($mValue);
            if ( $sub_idx ) {
                $items[$sub_key] = PjFreeze::buildKey($sub_idx);
            }
            else {
                $SubStatus = $this->_Status->appendPathTraversable($sub_key, $sub_idx);
                $Res = $this->_Serializer->serialize($mValue, $SubStatus);
                $items[$sub_key] = $Process->extractSerialized($Res);
            }
        }
        return $items;
    }
}