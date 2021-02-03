<?php

class Method
{
    public $code;
    public $name;
    public $type;
    public $link;
    public $country;
    public $logo_url;
    public $min_amount;
    public $max_amount;

    public function __construct($code, $name, $country, $type, $logo_url = "", $min_amount = 0, $max_amount = 0)
    {
        $this->code = $code;
        $this->name = $name;
        $this->country = $country;
        $this->type = $type;
        $this->logo_url = $logo_url;
        $this->min_amount = $min_amount;
        $this->max_amount = $max_amount;
    }

    public function getKey()
    {
        return sprintf('%s_%s', $this->code, $this->country);
    }
}
