<?php

/**
 * Extension for TextField that adds a counter
 *
 * @Author Martijn Schenk
 * @Alias  Chibby
 * @Email  martijnschenk@loyals.nl
 */
class CountableTextField extends TextField
{
    public function Field($properties = [])
    {
        $this->addExtraClass('countable text');

        Requirements::javascript(METACONFIG_DIR . '/javascript/countable.js');
        Requirements::css(METACONFIG_DIR . '/css/countable.css');

        return parent::Field($properties);
    }
}