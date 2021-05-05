<?php

namespace Ezpizee\SupportedCMS\Joomla;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Document\Document;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

class Properties
{
    /**
     * @var Document
     */
    public $jdoc;
    /**
     * @var Registry
     */
    public $menuParams;
    /**
     * @var HtmlDocument
     */
    public $htmlDoc;
    public $bodyClasses = '';
    public $siteName = '';
    public $tmpl = '';
    public $theme = '';
    public $webRoot = '';
    public $tmplWebRoot = '';
    public $langUrlPath = '';
    public $urlPathExt = '';
    public $bodyStyle = '';
    public $errors = [];
    public $isAuthed = false;

    public function __construct(HtmlDocument $htmlDoc, CMSApplication $app)
    {
        $this->isAuthed = !(Factory::getUser()->guest > 0);
        $this->jdoc = $app->getDocument();
        $this->siteName = $app->get('sitename');
        $this->htmlDoc = $htmlDoc;
        $this->menuParams = Menu::getActiveMenuParams();
        $this->tmpl = $this->menuParams->get('tmpl', $app->input->getString('tmpl', 'index.php'));
        $this->theme = $this->menuParams->get('theme', $app->input->getString('theme', 'main.php'));
        $this->webRoot = Uri::root();
        $this->tmplWebRoot = Uri::root(true).'/templates/'.$this->htmlDoc->template;
        $this->langUrlPath = explode('-', $this->jdoc->language)[0];
        if ($app->get('sef_suffix')) {$this->urlPathExt = '.html';}
        if ($this->menuParams->get('menu_image')) {
            $this->bodyStyle = ' style="background-image: url(\''.$this->menuParams->get('menu_image').'\')"';
        }
        $this->jdoc->setGenerator('');
        $this->errors = $app->getMessageQueue();
        $this->bodyClasses();
        $this->removePlatformClientLib();
        $this->setHeadTags();
    }

    public function hasModule($position): bool {
        if (is_numeric($position)) {
            return $this->htmlDoc->countModules('position-' . $position);
        }
        return $this->htmlDoc->countModules($position);
    }

    public function setHeadTags()
    {
        $tmplParams = $this->htmlDoc->params;
        if (!($tmplParams instanceof Registry)) {return;}

        $applicationName = $tmplParams->get('application-name', $this->siteName);
        $ogImageUrl = $this->menuParams->get('og-image-url', $tmplParams->get('og-image-url'));
        $noIndex = (int)$this->menuParams->get('noindex', $tmplParams->get('noindex', '0'));

        if ($this->menuParams->get('menu-meta_description')) {
            $this->htmlDoc->setDescription($this->menuParams->get('menu-meta_description'));
        }
        if ($noIndex > 0) {
            $this->htmlDoc->setMetaData('robots', 'noindex', 'name');
        }

        // og meta tags
        $this->htmlDoc->setMetaData('og:title', $this->htmlDoc->getTitle(), 'property');
        $this->htmlDoc->setMetaData('og:type', 'website', 'property');
        if ($this->menuParams->get('menu-meta_keywords')) {
            $this->htmlDoc->setMetaData('keywords', $this->menuParams->get('menu-meta_keywords'));
            $this->htmlDoc->setMetaData('og:keywords', $this->menuParams->get('menu-meta_keywords'), 'property');
        }
        $this->htmlDoc->setMetaData('og:description', $this->htmlDoc->getDescription(), 'property');
        $this->htmlDoc->setMetaData('og:site_name', $applicationName, 'property');
        if ($ogImageUrl) {
            $this->htmlDoc->setMetaData('og:image', $this->webRoot.$ogImageUrl, 'property');
        }
        $this->htmlDoc->setMetaData('og:url', Uri::current(), 'property');

        // twitter meta tags
        $this->htmlDoc->setMetaData('twitter:card', 'summary_large_image', 'property');
        $this->htmlDoc->setMetaData('twitter:title', $this->htmlDoc->getTitle(), 'property');
        $this->htmlDoc->setMetaData('twitter:description', $this->htmlDoc->getDescription(), 'property');
        if ($ogImageUrl) {
            $this->htmlDoc->setMetaData('twitter:image', $this->webRoot.$ogImageUrl, 'property');
        }

        $appleTouchIcon = $tmplParams->get('apple-touch-icon');
        if ($appleTouchIcon) {
            $this->htmlDoc->addHeadLink($appleTouchIcon, 'apple-touch-icon', 'rel', ['size' => '180x180']);
        }
        $favicon32 = $tmplParams->get('favicon-32');
        if ($favicon32) {
            $this->htmlDoc->addHeadLink($favicon32, 'icon', 'rel', ['type' => 'image/png', 'size' => '32x32']);
        }
        $favicon16 = $tmplParams->get('favicon-16');
        if ($favicon16) {
            $this->htmlDoc->addHeadLink($favicon16, 'icon', 'rel', ['type' => 'image/png', 'size' => '16x16']);
        }
        $favicon = $tmplParams->get('favicon');
        if ($favicon) {
            $this->htmlDoc->addHeadLink($favicon, 'shortcut icon', 'rel', ['type' => 'image/x-icon']);
        }
        $maskIcon = $tmplParams->get('mask-icon');
        if ($maskIcon) {
            $this->htmlDoc->addHeadLink($maskIcon, 'mask-icon', 'rel', ['color' => '#ffffff']);
        }
        $msApplicationTileImage = $tmplParams->get('msapplication-TileImage');
        if ($msApplicationTileImage) {
            $this->htmlDoc->setMetaData('msapplication-TileImage', $msApplicationTileImage);
        }
        $msApplicationTileColor = $tmplParams->get('msapplication-TileColor');
        if ($msApplicationTileColor) {
            $this->htmlDoc->setMetaData('msapplication-TileColor', $msApplicationTileColor);
        }
        $themeColor = $tmplParams->get('theme-color');
        if ($themeColor) {
            $this->htmlDoc->setMetaData('theme-color', $themeColor);
        }
        $this->htmlDoc->setMetaData('application-name', $applicationName);
    }

    private function bodyClasses(): void {
        $bodyClasses = [];
        $bodyClasses[] = str_replace('.php', '', $this->tmpl);
        $bodyClasses[] = str_replace('.php', '', $this->theme);
        $bodyClasses[] = $this->isAuthed ? 'is-authed' : 'is-public';
        $this->bodyClasses = implode(' ', $bodyClasses);
    }

    private function removePlatformClientLib(): void {
        $excludes = [
            '/media/jui/js/jquery.min.js',
            '/media/jui/js/jquery-noconflict.js',
            '/media/jui/js/jquery-migrate.min.js',
            '/media/jui/js/bootstrap.min.js'
        ];
        $scrips = [];
        foreach ($this->htmlDoc->_scripts as $src=>$type) {
            if (!in_array($src, $excludes)) {
                $scrips[$src] = $type;
            }
        }
        $this->htmlDoc->_scripts = $scrips;
    }
}