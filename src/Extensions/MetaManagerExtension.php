<?php

namespace Loyals\MetaConfig\Extensions;

use SilverStripe\CMS\Model\SiteTreeExtension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\ToggleCompositeField;
use SilverStripe\Forms\CheckboxField;
use Loyals\MetaConfig\Form\CountableTextField;
use Loyals\MetaConfig\Form\CountableTextareaField;
use Loyals\MetaConfig\Service\MetaGenerator;
use SilverStripe\Core\Convert;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\Core\Config\Config;
use SilverStripe\Security\Permission;
use SilverStripe\ErrorPage\ErrorPage;
use SilverStripe\Control\Director;
use SilverStripe\Control\ContentNegotiator;
use SilverStripe\ORM\CMSPreviewable;
use SilverStripe\CMS\Model\VirtualPage;


/**
 * Adds autoupdate of the metadatafields on pagesave.
 * A checkbox is added to the Main Content tab to select wether to update the MetaDescription and MetaKeyword fields on
 * save. Default is set to yes
 *
 * You can adjust the amount of keywords, minimal word character and wich words to exclude in _config.php
 *
 * @Author       Martijn van Nieuwenhoven
 * @Alias        Marvanni
 * @Email info@axyrmedia.nl
 *
 * @Silverstripe version 3
 * @package      MetaManagerExtension
 **/
class MetaManagerExtension extends SiteTreeExtension
{

    /**
     * @var int
     **/
    static $keyword_amount = 15;

    /**
     * @var int
     **/
    static $min_word_char = 4;

    /**
     * @var string
     **/
    static $exclude_words = '';

    /**
     * @var bool
     **/
    static $checkbox_state = 1;

    /**
     * @var int
     **/
    protected static $meta_desc_length = 255;

    /**
     * @var bool
     **/
    static $hide_extra_meta = 0;


    private static $db = [
        'MetaTitle'        => 'Varchar(255)',
        'GenerateMetaData' => 'Int',
    ];

    private static $defaults = [
        'GenerateMetaData' => 1,
    ];

    public function updateCMSFields(FieldList $fields)
    {

        $fields->removeByName([ 'Metadata' ]);

        $fields->addFieldToTab('Root.Main',
            ToggleCompositeField::create('Metadata',
                _t('SiteTree.MetadataToggle', 'Metadata'),
                [
                    new CheckboxField('GenerateMetaData',
                        _t('MetaManager.GENERATEMETADATA', 'Generate Meta-data automatically from the page content')),
                    new CountableTextField('MetaTitle', $this->owner->fieldLabel('MetaTitle')),
                    new CountableTextareaField('MetaDescription', $this->owner->fieldLabel('MetaDescription')),
                ]
            )->setHeadingLevel(4)
                ->setStartClosed(false)
        );

        $fields->replaceField('Metadata',
            ToggleCompositeField::create('Metadata',
                _t('SiteTree.MetadataToggle', 'Metadata'),
                null)->setStartClosed(false));

        if (self::$hide_extra_meta == 1) {
            $fields->removeByName('ExtraMeta');
            $fields->removeByName('ExtraMeta_original');
        }
    }

    /**
     * Update Metadata fields function
     */
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        if ($this->owner->ID && $this->owner->GenerateMetaData) {
            $this->owner->MetaTitle = strip_tags($this->owner->Title);

            $this->owner->MetaDescription = html_entity_decode(strip_tags($this->owner->Content), ENT_COMPAT, 'UTF-8');
            if (self::$meta_desc_length > 0 && strlen($this->owner->MetaDescription) > self::$meta_desc_length) {
                $this->owner->MetaDescription = mb_substr($this->owner->MetaDescription,
                        0,
                        self::$meta_desc_length,
                        'UTF-8') . "...";
            }
            // calculateKeywords
            $this->owner->MetaKeywords = MetaGenerator::generateKeywords(
                $this->owner->Content,
                self::$min_word_char,
                self::$keyword_amount,
                self::$exclude_words
            );
        }
    }

    /**
     * OVERLOAD MetaTags
     *
     * Return the title, description, keywords and language metatags.
     *
     * @todo Move <title> tag in separate getter for easier customization and more obvious usage
     *
     * @param boolean|string $includeTitle Show default <title>-tag, set to false for custom templating
     * @param boolean        $includeTitle Show default <title>-tag, set to false for
     *                                     custom templating
     *
     * @return string The XHTML metatags
     *
     *
     * Extend with updateMetaTags, e.g.:
     *
     * public function updateMetaTags(&$tags) {
     *     $tags['x-custom-stuff'] => $this->SomethingSpecial();
     * }
     *
     * Note: You can override tags by key, e.g. $['title'] => $this->MyCustomTitleFunction();
     */
    public function MetaTags(&$tags)
    {
        $metatags = [ ];

        // can't use $includeTitle here (missing in extend call @ SiteTree.php) --> find <title> in $tags
        if (strpos($tags, '<title>') !== false) {
            $metatags['title'] = "<title>" . Convert::raw2xml(($this->owner->MetaTitle)
                    ? $this->owner->MetaTitle
                    : $this->owner->Title) . " - " . Convert::raw2xml(SiteConfig::current_site_config()->Title) . "</title>";
        }

        $charset = Config::inst()->get(ContentNegotiator::class, 'encoding');
        $metatags['ContentType'] = "<meta http-equiv=\"Content-type\" content=\"text/html; charset=$charset\" />";
        if ($this->owner->MetaDescription) {
            $metatags['MetaDescription'] = "<meta name=\"description\" content=\"" . Convert::raw2att($this->owner->MetaDescription) . "\" />";
        }
        if ($this->owner->ExtraMeta) {
            $metatags['ExtraMeta'] = $this->owner->ExtraMeta;
        }

        if (Permission::check('CMS_ACCESS_CMSMain') && in_array(CMSPreviewable::class,
                class_implements($this->owner)) && !$this->owner instanceof ErrorPage
        ) {
            $metatags['x-page-id'] = "<meta name=\"x-page-id\" content=\"{$this->owner->ID}\" />";
            $metatags['x-cms-edit-link'] = "<meta name=\"x-cms-edit-link\" content=\"" . $this->owner->CMSEditLink() . "\" />";
        }

        if (Director::isLive()) {
            $metatags['robots'] = "<meta name=\"robots\" content=\"index,follow\" />";
        } else {
            $metatags['robots'] = "<meta name=\"robots\" content=\"noindex,nofollow\" />";
        }

        $className = $this->getClassShortName(get_class($this->owner));
        if ($className === VirtualPage::class && $this->owner->CopyContentFrom()->ID) {
            $metatags['canonical'] = "<link rel=\"canonical\" href=\"{$this->owner->CopyContentFrom()->Link()}\" />\n";
        }

        // Extend to updateMetaTags(), can't extend to owner->MetaTags()
        $this->owner->extend('updateMetaTags', $metatags);

        $tags = implode("\n", $metatags);
    }

    /**
     * @param $className
     * @return string
     */
    protected function getClassShortName($className)
    {
        try {
            $reflect = new \ReflectionClass($className ?? '');
            return $reflect->getShortName();
        } catch (ReflectionException $e) {
        }

        // fallback
        $parts = explode('\\', $className ?? '');
        return array_shift($parts);
    }
}
