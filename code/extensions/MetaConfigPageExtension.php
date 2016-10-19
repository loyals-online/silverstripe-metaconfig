<?php

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
    private static $db = array(
        'NoIndex'           => 'Boolean',
        'NoFollow'          => 'Boolean',
    );

    /**
     * Retrieve the site config
     *
     * @return \DataObject
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
     * @param \FieldList
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
     * @return \HTMLText
     */
    public function GoogleRichSnippetLocalBusiness()
    {
        $siteConfig = $this->getSiteConfig();
        $image      = $this->getPageImage();

        $addressString = urlencode(sprintf(
            '%1$s, %2$s, %3$s, NL',
            $siteConfig->Address,
            $siteConfig->City,
            $siteConfig->Postcode
        ));

        $geocoding = GoogleGeocoding::getOrCreateGeocode($addressString);

        $snippet = [
            '@context' => 'http://schema.org',
            '@type'    => $siteConfig->BusinessType,
            'image'    => $image ? Director::absoluteBaseURL() . $image->Link() : null, // we'll need to fix this
            '@id'      => Director::absoluteBaseURL(),
            'name'     => $siteConfig->Title,
            'address'  => [
                '@type'           => 'PostalAddress',
                'streetAddress'   => $siteConfig->Address,
                'addressLocality' => $siteConfig->City,
                'postalCode'      => $siteConfig->Postcode,
                'addressCountry'  => 'NL',
            ],
        ];

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

        return $template->process($this->owner->customise(new ArrayData([
            "JSON" => json_encode($snippet),
        ])));
    }

    /**
     * Generate OpenGraph meta data
     *
     * @return \HTMLText
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
            'Image'       => ($image ? Director::absoluteBaseURL() . $image->FocusCropHeight(249)
                    ->Link() : null),
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
     * @return \HTMLText
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
            'Image'       => Director::absoluteBaseURL() . $image->FocusCropHeight($product ? '281' : '125')
                    ->Link(),
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
     * @return \HTMLText
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
     * @return \HTMLText
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
     * @return \HTMLText
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

    /**
     * Returns true if the uploader is being used in CMS context
     *
     * @return boolean
     */
    protected function isCMS()
    {
        return Controller::curr() instanceof LeftAndMain;
    }
}