<?php
namespace MaciejSzUtFix\PjFreeze\Forum;

abstract class AEntry
{
    /** @var null|User */
    public $Author;

    /** @var string  */
    public $title = "";

    /** @var string  */
    public $contents = "";

    /**
     * @param string $title
     * @param string $contents
     */
    public function __construct($title = null, $contents = null)
    {
        $this->title = $title;
        $this->contents = $contents;
    }
}