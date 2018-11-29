<?php

namespace Leankoala\SuluDocBundle\Twig;

use Sulu\Bundle\WebsiteBundle\Navigation\NavigationMapperInterface;
use Sulu\Component\Content\Mapper\ContentMapperInterface;
use Sulu\Component\Webspace\Analyzer\RequestAnalyzerInterface;
use Symfony\Component\DependencyInjection\Container;

class SectionExtension extends \Twig_Extension
{
    public function __construct(
        ContentMapperInterface $contentMapper,
        NavigationMapperInterface $navigationMapper,
        RequestAnalyzerInterface $requestAnalyzer = null
    )
    {
        $this->contentMapper = $contentMapper;
        $this->navigationMapper = $navigationMapper;
        $this->requestAnalyzer = $requestAnalyzer;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_Function('sulu_doc_section', array($this, 'getSection')),
            new \Twig_Function('sulu_doc_home', array($this, 'getHomepage')),
            new \Twig_Function('sulu_doc_slug', array($this, 'getSlug')),
        );
    }

    /**
     * @param $uuid
     * @return Sulu\Bundle\WebsiteBundle\Navigation\NavigationItem
     */
    public function getSection($uuid)
    {
        $webspaceKey = $this->requestAnalyzer->getWebspace()->getKey();
        $locale = $this->requestAnalyzer->getCurrentLocalization()->getLocale();

        $breadcrumbElements = $this->navigationMapper->getBreadcrumb(
            $uuid,
            $webspaceKey,
            $locale
        );

        if (count($breadcrumbElements) > 1) {
            return $breadcrumbElements[1];
        } else {
            return $breadcrumbElements[0];
        }
    }

    public function getHomepage()
    {
        $webspaceKey = $this->requestAnalyzer->getWebspace()->getKey();
        $locale = $this->requestAnalyzer->getCurrentLocalization()->getLocale();

        $rootNavigation = $this->navigationMapper->getRootNavigation($webspaceKey, $locale);

        return array_pop($rootNavigation);
    }

    public function getName()
    {
        return "sulu_doc_section";
    }

    public static function getSlug($text)
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, '-');

        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }
}
