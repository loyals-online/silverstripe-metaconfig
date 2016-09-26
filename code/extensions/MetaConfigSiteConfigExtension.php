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
    private static $db = array(
        'Organization'       => 'Varchar',
        'BusinessType'       => 'Varchar',
        'Address'            => 'Varchar',
        'Postcode'           => 'Varchar',
        'City'               => 'Varchar',
        'Phonenumber'        => 'Varchar',
        'EmailAddress'       => 'Varchar',
        'TwitterUser'        => 'Varchar',

        'FacebookPageLink'   => 'Varchar',
        'TwitterLink'        => 'Varchar',
        'YoutubeLink'        => 'Varchar',

        'GoogleAnalyticsID'  => 'Varchar',
        'GoogleTagManagerID' => 'Varchar',

        'BodyScripts'        => 'Text',

        'RobotsText' => 'Text',

    );

    private static $has_one = [
        'Image' => 'Image',
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName('Theme');

        $fields->addFieldsToTab("Root.Main", array(
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

            HeaderField::create(_t('SiteConfig.SocialMedia', 'Social media')),
            TextField::create('FacebookPageLink', _t('SiteConfig.FacebookPageLink', 'Facebook page link')),
            TextField::create('TwitterLink', _t('SiteConfig.TwitterLink', 'Twitter link')),
            TextField::create('YoutubeLink', _t('SiteConfig.YoutubeLink', 'Youtube channel link')),

            HeaderField::create(_t('SiteConfig.Google', 'Google')),
            TextField::create('GoogleAnalyticsID', _t('SiteConfig.GoogleAnalyticsID', 'Google Analytics ID')),
            TextField::create('GoogleTagManagerID', _t('SiteConfig.GoogleTagManagerID', 'Google Tag Manager ID')),

            HeaderField::create(_t('SiteConfig.BodyScriptsHeader', 'Scripts')),
            TextareaField::create('BodyScripts', _t('SiteConfig.BodyScripts', 'Body Scripts'))
                ->setRows(20),

            HeaderField::create(_t('SiteConfig.RobotTextsHeader', 'Robots')),
            TextareaField::create('RobotsText', _t('SiteConfig.RobotsText', 'Robots.txt'))
                ->setRows(20),
        ));

        return $fields;
    }
}