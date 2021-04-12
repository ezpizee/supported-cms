<?php

namespace Ezpizee\SupportedCMS\Joomla;

use Exception;
use Ezpizee\SupportedCMS\Exception\Error;
use Joomla\CMS\MVC\Controller\BaseController;

class Controller
{
    /**
     * @var BaseController
     */
    private static $instance = null;

    public static function getContentInstance() {
        if (self::$instance === null) {
            try {
                self::$instance = BaseController::getInstance('Content');
            }
            catch (Exception $e) {
                new Error($e->getMessage(), 500);
            }
        }
        return self::$instance;
    }
}