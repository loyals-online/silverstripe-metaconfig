<?php

namespace Loyals\MetaConfig\Extensions;

use SilverStripe\Assets\Image;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Control\Director;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Convert;
use Loyals\MetaConfig\Model\GoogleGeocoding;
use SilverStripe\View\SSViewer;
use SilverStripe\View\ArrayData;

/**
 * Extension for Page that adds a lot of meta functionality
 *
 * @Author Martijn Schenk
 * @Alias  Chibby
 * @Email  martijnschenk@loyals.nl
 */
class MetaConfigPageExtension extends DataExtension
{
    /**
     * Cache for the page image
     *
     * @var Image|null
     */
    protected $image;

    /**
     * Cache for the site image
     *
     * @var SiteConfig|null
     */
    protected $config;

    /**
     * Database fields
     *
     * @var array
     */
    private static $db = [
        'NoIndex'  => 'Boolean',
        'NoFollow' => 'Boolean',
    ];

    /**
     * Retrieve the site config
     *
     * @return DataObject
     */
    protected function getSiteConfig()
    {
        if (!$this->config) {
            $this->config = SiteConfig::current_site_config();
        }

        return $this->config;
    }

    /**
     * Update the settings tab
     *
     * @param FieldList
     */
    public function updateSettingsFields(FieldList &$fields)
    {
        $fields->addFieldToTab('Root.Settings', CompositeField::create($robots = FieldGroup::create(checkBoxField::create('NoIndex', _t('Admin.NoIndex', 'No index')), checkBoxField::create('NoFollow', _t('Admin.NoFollow', 'No follow')))));
        $robots->setTitle(_t('Admin.Robots', 'Robots'));

    }

    /**
     * Update the meta tags
     *
     * @param $metatags
     */
    public function updateMetaTags(&$metatags)
    {
        if (Director::isLive()) {
            $index  = $this->owner->NoIndex ? 'noindex' : 'index';
            $follow = $this->owner->NoFollow ? 'nofollow' : 'follow';

            $metatags['robots'] = sprintf("<meta name=\"robots\" content=\"%1\$s,%2\$s\" />", $index, $follow);
        }

        if (method_exists(Controller::curr(), 'getProduct') && Controller::curr()
                ->getProduct() &&
            Controller::curr()
                ->getProduct()
                ->Introduction) {
            $metatags['MetaDescription'] = "<meta name=\"description\" content=\"" . Convert::raw2att(Controller::curr()
                    ->getProduct()
                    ->MetaDescription) . "\" />";
        }
    }

    /**
     * Generate a Google Rich Snippet for Local Business
     *
     * @return DBHTMLText
     */
    public function GoogleRichSnippetLocalBusiness()
    {
        $siteConfig = $this->getSiteConfig();

        $addressString = urlencode(
            sprintf(
                '%1$s, %2$s, %3$s, NL',
                $siteConfig->Address,
                $siteConfig->City,
                $siteConfig->Postcode
            )
        );

        $geocoding = GoogleGeocoding::getOrCreateGeocode($addressString);

        $snippet = [
            '@context' => 'http://schema.org',
            '@type'    => 'LocalBusiness',
            'image'    => Director::absoluteURL('/themes/EHA/img/icons/og-image-200x200.png'), // we'll need to fix this
            'logo'     => Director::absoluteURL('/themes/EHA/img/icons/og-image-200x200.png'), // we'll need to fix this
            '@id'      => Director::absoluteBaseURL(),
            'name'     => $siteConfig->Organization,
            'address'  => [
                '@type'           => 'PostalAddress',
                'streetAddress'   => $siteConfig->Address,
                'addressLocality' => $siteConfig->City,
                'postalCode'      => $siteConfig->Postcode,
                'addressCountry'  => 'NL',
            ],
        ];

        /** @var Image $image */
        if (($image = SiteConfig::current_site_config()
                ->Image()) && $image->exists()) {
            $snippet['logo'] = $image->AbsoluteLink();
        }

        /** @var Image $image */
        if (($image = $this->getPageImage()) && $image->exists()) {
            $snippet['image'] = $image->AbsoluteLink();
        }

        if ($geocoding) {
            $snippet['geo'] = [
                '@type'     => 'GeoCoordinates',
                'latitude'  => $geocoding->Latitude,
                'longitude' => $geocoding->Longitude,
            ];
        }

        $snippet['url']       = Director::absoluteBaseURL();
        $snippet['telephone'] = $siteConfig->Phonenumber;

        $template = new SSViewer('GoogleRichSnippetLocalBusiness');

        return $template->process(
            $this->owner->customise(
                new ArrayData(
                    [
                        "JSON" => json_encode($snippet),
                    ]
                )
            )
        );
    }

    /**
     * Generate OpenGraph meta data
     *
     * @return DBHTMLText
     */
    public function OpenGraph()
    {
        $siteConfig = $this->getSiteConfig();
        $image      = $this->getPageImage();

        /** @var SimpleProduct $product */
        $product = method_exists(Controller::curr(), 'getProduct') && Controller::curr()
                ->getProduct();

        $template = new SSViewer('OpenGraph');

        return $template->process($this->owner->customise(new ArrayData([
            'Title'       => $this->owner->Title,
            'Type'        => 'website',
            'Image'       => ($image && $image->exists() && ($croppedImage = $image->CropHeight(249))
                ? $croppedImage->getAbsoluteURL()
                : null),
            'Url'         => $this->owner->AbsoluteLink(),
            'SiteName'    => $siteConfig->Title,
            'ThemeDir'    => $this->owner->ThemeDir(),
            'Description' => $product ?
                $product->MetaDescription :
                ($this->owner->MetaDescription ?: ''),
        ])));
    }

    /**
     * Generate a Twitter Summary Card
     *
     * @return DBHTMLText
     */
    public function TwitterSummaryCard()
    {
        $siteConfig = $this->getSiteConfig();
        $image      = $this->getPageImage();

        /** @var SimpleProduct $product */
        $product = method_exists(Controller::curr(), 'getProduct') && Controller::curr()
                ->getProduct();

        $template = new SSViewer('TwitterSummaryCard');

        return $template->process($this->owner->customise(new ArrayData([
            'Type'        => $product ? 'summary_large_image' : 'summary',
            'Title'       => $product ? $product->Title : $this->owner->Title,
            'Image'       => ($image && $image->exists() && ($croppedImage = $image->CropHeight($product ? '281' : '125'))
                ? $croppedImage->getAbsoluteURL()
                : null),
            'TwitterUser' => $siteConfig->TwitterUser,
            'Description' => $product ?
                $product->MetaDescription :
                ($this->owner->MetaDescription ?: ''),
        ])));
    }

    /**
     * Retrieve the image for this page
     *
     * @return null|Image
     */
    protected function getPageImage()
    {
        $siteConfig = $this->getSiteConfig();
        if (!$this->image) {
            if (method_exists($this->owner, 'getPageImage')) {
                $this->image = $this->owner->getPageImage();
            } else {
                $this->image = $siteConfig->Image();
            }
        }

        return $this->image;
    }

    /**
     * Retrieve the Google scripts for this page
     *
     * @return DBHTMLText
     */
    public function GoogleScripts()
    {
        $siteConfig = $this->getSiteConfig();

        $template = new SSViewer('GoogleScripts');

        return $template->process($this->owner->customise(new ArrayData([
            'AnalyticsID'  => $siteConfig->GoogleAnalyticsID,
            'TagManagerID' => $siteConfig->GoogleTagManagerID,
        ])));
    }

    /**
     * Retrieve the Google breadcrumbs for this page
     *
     * @return DBHTMLText
     */
    public function GoogleBreadcrumbs()
    {
        $template = new SSViewer('GoogleBreadcrumbs');

        return $template->process($this->owner->customise(new ArrayData([
            'JSON' => json_encode($this->getGoogleBreadcrumbs()),
        ])));
    }

    /**
     * Retrieve the Breadcrumbs information in a schema.org defined array
     *
     * @return array|bool
     */
    protected function getGoogleBreadcrumbs()
    {
        $crumbs = $this->owner->getBreadcrumbItems(20, false, true);

        if (!$crumbs) {
            return false;
        }

        $return = [
            "@context" => "http://schema.org",
            "@type"    => "BreadcrumbList",
        ];

        /**
         * @var int $idx
         * @var Page $crumb
         */
        foreach ($crumbs AS $idx => $crumb) {
            if (!isset($return["itemListElement"])) {
                $return["itemListElement"] = [];
            }
            $return["itemListElement"][] =
                [
                    "@type"    => "ListItem",
                    "position" => $idx + 1,
                    "item"     => [
                        "@id"  => $crumb->AbsoluteLink(),
                        "name" => $crumb->dbObject('MenuTitle')->XML(),
                    ],
                ];
        }

        return $return;
    }

    /**
     * Retrieve the canonical, next and previous link tags
     *
     * @return DBHTMLText
     */
    public function Canonical()
    {
        $template = new SSViewer('Canonical');

        return $template->process($this->owner->customise(new ArrayData([
            'Canonical' => $this->owner->AbsoluteLink(),
            'Next'      => $this->NextLink(),
            'Previous'  => $this->PrevLink(),
        ])));
    }

    /**
     * Attempt to retrieve a next link
     *
     * @return string|bool
     */
    protected function NextLink()
    {
        if (method_exists($this->owner, 'NextLink')) {
            return $this->owner->NextLink();
        }
        return false;
    }

    /**
     * Attempt to retrieve a previous link
     *
     * @return string|bool
     */
    protected function PrevLink()
    {
        if (method_exists($this->owner, 'PrevLink')) {
            return $this->owner->PrevLink();
        }
        return false;
    }
}
