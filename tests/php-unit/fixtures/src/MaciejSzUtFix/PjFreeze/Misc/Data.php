<?php
namespace MaciejSzUtFix\PjFreeze\Misc;

class Data
{
    public $a = "a";
    public $Container;
    public $b = "b";

    /**
     * @param Container $Container
     */
    public function __construct($Container)
    {
        $this->Container = $Container;
    }

}