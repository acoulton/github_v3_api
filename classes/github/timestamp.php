<?php
/**
 * All timestamp values received from Github are converted to this object
 * representation to facilitate date/time operations and timezone support
 */
class Github_Timestamp extends DateTime
{
    public function __construct($github, $data)
    {
		parent::__construct($data);
    }
    
    public function __toString()
    {
        return $this->format('c');
    }
}