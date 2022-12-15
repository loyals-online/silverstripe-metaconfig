<?php

namespace Loyals\MetaConfig\Controller;

use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\CMS\Model\SiteTree;


/**
 * Provides robots.txt functionality
 */
class Robots extends Controller
{

    /**
     * Determines if this is a public site
     *
     * @return boolean flag indicating if this robots is for a public site
     */
    protected function isPublic()
    {
        return Director::isLive();
    }

    /**
     * Generates the response containing the robots.txt content
     *
     * @return HTTPResponse
     * @throws \SilverStripe\ORM\ValidationException
     */
    public function index()
    {
        $siteConfig = SiteConfig::current_site_config();

        //check of empty, if so, generate and save
        if (!$siteConfig->RobotsText) {
            $siteConfig->RobotsText = $this->generateText();
            if ($this->isPublic()) {
                $siteConfig->write();
            }
        }

        $text = $siteConfig->RobotsText;

        $response = new HTTPResponse($text, 200);
        $response->addHeader("Content-Type", "text/plain; charset=\"utf-8\"");

        return $response;
    }

    protected function generateText()
    {
        $text = "";
        $text .= $this->renderSitemap();
        $text .= "User-agent: *\n";
        $text .= $this->renderDisallow();
        $text .= $this->renderAllow();

        return $text;
    }

    /**
     * Renders the sitemap link reference
     *
     * @return string
     */
    protected function renderSitemap()
    {

        // No sitemap if not public
        if (!$this->isPublic()) {
            return '';
        }

        // Check if sitemap is configured
        $sitemap = Robots::config()->sitemap;
        if (empty($sitemap)) {
            return '';
        }

        // Skip sitemap if not available
        if (!class_exists(\Wilr\GoogleSitemaps\GoogleSitemap::class) && !Director::fileExists($sitemap)) {
            return '';
        }

        // Report the sitemap location
        return sprintf("Sitemap: %s\n", Director::absoluteURL($sitemap));
    }

    /**
     * Renders the list of disallowed pages
     *
     * @return string
     */
    protected function renderDisallow()
    {
        // List only disallowed urls
        $text = '';
        foreach ($this->disallowedUrls() as $url) {
            $text .= sprintf("Disallow: %s\n", $url);
        }

        return $text;
    }

    /**
     * Renders the list of allowed pages, if any
     *
     * @return string
     */
    protected function renderAllow()
    {
        $text = '';
        foreach ($this->allowedUrls() as $url) {
            $text .= sprintf("Allow: %s\n", $url);
        }

        return $text;
    }

    /**
     * Returns an array of disallowed URLs
     *
     * @return array
     */
    protected function disallowedUrls()
    {

        // If not public, disallow all
        if (!$this->isPublic()) {
            return ["/"];
        }

        // Get configured disallowed urls
        $urls = (array) Robots::config()->disallowed_urls;

        // Add all pages where ShowInSearch is false
        if (Robots::config()->disallow_unsearchable) {
            $unsearchablePages = SiteTree::get()
                ->where('"SiteTree"."ShowInSearch" = 0');
            foreach ($unsearchablePages as $page) {
                $link = $page->Link();
                // Don't disallow home page
                if ($link !== '/') {
                    $urls[] = $link;
                }
            }
        }

        return array_unique($urls);
    }

    /**
     * Returns an array of allowed URLs
     *
     * @return array
     */
    protected function allowedUrls()
    {
        return (array) Robots::config()->allowed_urls;
    }

}
