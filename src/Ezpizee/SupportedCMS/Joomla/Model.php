<?php

namespace Ezpizee\SupportedCMS\Joomla;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class Model
{
    /**
     * @var BaseDatabaseModel
     */
    private static $contentArticleInstance = null;

    public static function getContentArticle(): BaseDatabaseModel {
        if (self::$contentArticleInstance === null) {
            self::$contentArticleInstance = Controller::getContentInstance()->getModel('Article');
        }
        return self::$contentArticleInstance;
    }
}