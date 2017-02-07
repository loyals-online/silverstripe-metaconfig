<?php

/**
 * Extension for SiteConfig that adds basic meta information
 *
 * @Author Martijn Schenk
 * @Alias  Chibby
 * @Email  martijnschenk@loyals.nl
 */
class MetaConfigSiteConfigExtension extends DataExtension
{
    private static $db = [
        'Organization'       => 'Varchar',
        'BusinessType'       => 'Varchar',
        'Address'            => 'Varchar',
        'Postcode'           => 'Varchar',
        'City'               => 'Varchar',
        'Phonenumber'        => 'Varchar',
        'EmailAddress'       => 'Varchar(255)',
        'TwitterUser'        => 'Varchar',

        // business
        'COCNumber'          => 'Varchar', // Chamber of Commerce / KvK
        'VatNumber'          => 'Varchar', // Value Added Tax / BTW
        'IBAN'               => 'Varchar',

        // social
        'FacebookPageLink'   => 'Varchar(255)',
        'TwitterLink'        => 'Varchar(255)',
        'YoutubeLink'        => 'Varchar(255)',
        'LinkedInLink'       => 'Varchar(255)',

        // google
        'GoogleAnalyticsID'  => 'Varchar',
        'GoogleTagManagerID' => 'Varchar',

        // javascript
        'BodyScripts'        => 'Text',

        // robots
        'RobotsText'         => 'Text',

    ];

    private static $has_one = [
        'Image' => 'Image',
    ];

    private static $language_config;
    private static $current_locale;

    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName('Theme');

        $fields->addFieldsToTab("Root.Main", [
            HeaderField::create(_t('SiteConfig.OrganizationDetails', 'Organization details')),
            TextField::create('Organization', _t('SiteConfig.Organization', 'Organization')),
            TextField::create('BusinessType', _t('SiteConfig.BusinessType', 'Business type')),
            TextField::create('Address', _t('SiteConfig.Address', 'Address')),
            TextField::create('Postcode', _t('SiteConfig.Postcode', 'Postcode')),
            TextField::create('City', _t('SiteConfig.City', 'City')),
            TextField::create('Phonenumber', _t('SiteConfig.Phonenumber', 'Phone number')),
            TextField::create('EmailAddress', _t('SiteConfig.EmailAddress', 'Email address')),
            TextField::create('TwitterUser', _t('SiteConfig.TwitterUser', 'Twitter username')),
            UploadField::create('Image', _t('SiteConfig.DefaultImage', 'Default image'))
                ->setFolderName('fallback')
                ->setDisplayFolderName('fallback'),

            HeaderField::create(_t('SiteConfig.Business', 'Business data')),
            TextField::create('COCNumber', _t('SiteConfig.COCNumber', 'CoC Number')),
            TextField::create('VATNumber', _t('SiteConfig.VATNumber', 'Vat Number')),
            TextField::create('IBAN', _t('SiteConfig.IBAN', 'IBAN')),

            HeaderField::create(_t('SiteConfig.SocialMedia', 'Social media')),
            TextField::create('FacebookPageLink', _t('SiteConfig.FacebookPageLink', 'Facebook page link')),
            TextField::create('TwitterLink', _t('SiteConfig.TwitterLink', 'Twitter link')),
            TextField::create('YoutubeLink', _t('SiteConfig.YoutubeLink', 'Youtube channel link')),
            TextField::create('LinkedInLink', _t('SiteConfig.LinkedInLink', 'LinkedIn page link')),

            HeaderField::create(_t('SiteConfig.Google', 'Google')),
            TextField::create('GoogleAnalyticsID', _t('SiteConfig.GoogleAnalyticsID', 'Google Analytics ID')),
            TextField::create('GoogleTagManagerID', _t('SiteConfig.GoogleTagManagerID', 'Google Tag Manager ID')),

            HeaderField::create(_t('SiteConfig.BodyScriptsHeader', 'Scripts')),
            TextareaField::create('BodyScripts', _t('SiteConfig.BodyScripts', 'Body Scripts'))
                ->setRows(20),

            HeaderField::create(_t('SiteConfig.RobotTextsHeader', 'Robots')),
            TextareaField::create('RobotsText', _t('SiteConfig.RobotsText', 'Robots.txt'))
                ->setRows(20),
        ]);

        return $fields;
    }

    public function getSiteDomain()
    {
        if ($config = $this->getLangConfig()) {
            return $config['domain'];
        }
    }

    public function getSiteDescription()
    {
        if ($config = $this->getLangConfig()) {
            return $config['description'];
        }
    }

    public function getLocaleFromHost($host = null)
    {
        if ($host && $locale = $this->SearchLocale($host)) {
            return $locale;
        }
    }

    public function getDomainLocales()
    {
        if (!self::$language_config) {
            $config = Config::inst()
                ->get('Environment', 'default');

            if (isset($config['DomainLocales'])) {
                self::$language_config = $config['DomainLocales'];
            }
        }

        return self::$language_config;
    }

    public function getLangConfig()
    {
        $config = $this->getDomainLocales();

        if ($config && isset($config[$this->getCurrentLocale()])) {
            return $config[$this->getCurrentLocale()];
        }
    }

    public function getCurrentLocale()
    {
        if (!self::$current_locale && class_exists('Translatable')) {
            self::$current_locale = Translatable::get_current_locale();
        }

        return self::$current_locale;
    }

    protected function SearchLocale($searchdomain = null)
    {
        if ($searchdomain && $config = $this->getDomainLocales()) {
            foreach ($config as $locale => $domain) {
                if ($domain['domain'] === $searchdomain) {
                    return $locale;
                }
            }
        }
    }
}