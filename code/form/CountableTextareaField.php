<?php

/**
 * Extension for TextareaField that adds a counter
 *
 * @Author Martijn Schenk
 * @Alias  Chibby
 * @Email  martijnschenk@loyals.nl
 */
class CountableTextareaField extends TextareaField
{
    public function Field($properties = [])
    {
        $this->addExtraClass('countable textarea');

        Requirements::javascript(METACONFIG_DIR . '/javascript/countable.js');
        Requirements::css(METACONFIG_DIR . '/css/countable.css');

        return parent::Field($properties);
    }
}