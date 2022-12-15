<?php

namespace Loyals\MetaConfig\Form;

use SilverStripe\Forms\TextField;
use SilverStripe\View\Requirements;

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

        Requirements::javascript('eha/metaconfig:javascript/countable.js');
        Requirements::css('eha/metaconfig:css/countable.css');

        return parent::Field($properties);
    }
}
