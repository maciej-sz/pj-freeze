<?php
namespace MaciejSz\PjFreeze\Exc;

use Exception;

class EVersionMismatch extends \Exception
{
    public function __construct($got, $incoming)
    {
        parent::__construct("Version mismatch. Got: \"{$got}\" incoming: \"{$incoming}\"");
    }

}