<?php
namespace MaciejSzUtFix\PjFreeze\Forum;

class User
{
    const SERIAL_VERSION_UID = 123;

    /** @var  string */
    public $name = "";

    /** @var string  */
    public $email = "";

    /** @var string  */
    public $joined = "";

    /** @var array|AEntry[] */
    public $entries = [];

    /**
     * @param string $name
     * @param string $email
     * @param string $joined
     */
    public function __construct($name = null, $email = null, $joined = null)
    {
        $this->name = $name;
        $this->email = $email;
        $this->joined = $joined;
    }
}