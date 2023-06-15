<?php

namespace Loyals\MetaConfig\Form;

use SilverStripe\Forms\TextareaField;
use SilverStripe\View\Requirements;

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

        Requirements::javascript('mediaweb/silverstripe-metaconfig:javascript/countable.js');
        Requirements::css('mediaweb/silverstripe-metaconfig:css/countable.css');

        return parent::Field($properties);
    }
}
