<?php
namespace MaciejSzUtFix\PjFreeze\Misc;

class Container
{
    protected $Data1;
    protected $Data2;

    /**
     */
    public function __construct()
    {
        $Data = new Data($this);
        $this->Data1 = $Data;
        $this->Data2 = $Data;
    }


}