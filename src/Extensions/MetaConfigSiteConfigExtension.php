<?php

namespace Loyals\MetaConfig\Extensions;

use SilverStripe\Assets\Image;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\TextField;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Forms\TextareaField;


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
        'InstagramLink'       => 'Varchar(255)',

        // google
        'GoogleAnalyticsID'  => 'Varchar',
        'GoogleTagManagerID' => 'Varchar',

        // javascript
        'BodyScripts'        => 'Text',

        // robots
        'RobotsText'         => 'Text',

    ];

    private static $has_one = [
        'Image' => Image::class,
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName('Theme');

        $fields->addFieldsToTab("Root.Main", [
            HeaderField::create('OrganizationDetails', _t('SiteConfig.OrganizationDetails', 'Organization details')),
            TextField::create('Organization', _t('SiteConfig.Organization', 'Organization')),
            TextField::create('BusinessType', _t('SiteConfig.BusinessType', 'Business type')),
            TextField::create('Address', _t('SiteConfig.Address', 'Address')),
            TextField::create('Postcode', _t('SiteConfig.Postcode', 'Postcode')),
            TextField::create('City', _t('SiteConfig.City', 'City')),
            TextField::create('Phonenumber', _t('SiteConfig.Phonenumber', 'Phone number')),
            TextField::create('EmailAddress', _t('SiteConfig.EmailAddress', 'Email address')),
            TextField::create('TwitterUser', _t('SiteConfig.TwitterUser', 'Twitter username')),
            UploadField::create(Image::class, _t('SiteConfig.DefaultImage', 'Default image'))
                ->setFolderName('fallback'),

            HeaderField::create('BusinessData', _t('SiteConfig.Business', 'Business data')),
            TextField::create('COCNumber', _t('SiteConfig.COCNumber', 'CoC Number')),
            TextField::create('VatNumber', _t('SiteConfig.VATNumber', 'Vat Number')),
            TextField::create('IBAN', _t('SiteConfig.IBAN', 'IBAN')),

            HeaderField::create('SocialMedia', _t('SiteConfig.SocialMedia', 'Social media')),
            TextField::create('FacebookPageLink', _t('SiteConfig.FacebookPageLink', 'Facebook page link')),
            TextField::create('TwitterLink', _t('SiteConfig.TwitterLink', 'Twitter link')),
            TextField::create('YoutubeLink', _t('SiteConfig.YoutubeLink', 'Youtube channel link')),
            TextField::create('LinkedInLink', _t('SiteConfig.LinkedInLink', 'LinkedIn page link')),
            TextField::create('InstagramLink', _t('SiteConfig.InstagramLink', 'Instagram page link')),

            HeaderField::create('Google', _t('SiteConfig.Google', 'Google')),
            TextField::create('GoogleAnalyticsID', _t('SiteConfig.GoogleAnalyticsID', 'Google Analytics ID')),
            TextField::create('GoogleTagManagerID', _t('SiteConfig.GoogleTagManagerID', 'Google Tag Manager ID')),

            HeaderField::create('Scripts', _t('SiteConfig.BodyScriptsHeader', 'Scripts')),
            TextareaField::create('BodyScripts', _t('SiteConfig.BodyScripts', 'Body Scripts'))
                ->setRows(20),

            HeaderField::create('Robots', _t('SiteConfig.RobotTextsHeader', 'Robots')),
            TextareaField::create('RobotsText', _t('SiteConfig.RobotsText', 'Robots.txt'))
                ->setRows(20),
        ]);

        return $fields;
    }
}
