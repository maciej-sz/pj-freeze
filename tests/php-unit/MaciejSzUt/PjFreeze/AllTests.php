<?php
namespace MaciejSzUt\PjFreeze;

class AllTests extends \PHPUnit_Framework_TestSuite
{
    public static function suite()
    {
        $Suite = new self();
        return $Suite;
    }
}