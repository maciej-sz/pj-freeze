<?php
namespace MaciejSzUt\PjFreeze;

class AllTests extends \PHPUnit_Framework_TestSuite
{
    public static function suite()
    {
        $Suite = new self();

        $Suite->addTestSuite(BasicSerializationTest::class);
        $Suite->addTestSuite(BasicUnserializationTest::class);
        $Suite->addTestSuite(EncodingTest::class);

        return $Suite;
    }
}