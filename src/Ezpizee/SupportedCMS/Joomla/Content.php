<?php

namespace Ezpizee\SupportedCMS\Joomla;

use stdClass;

class Content
{
    public static function getArticleById(int $id): stdClass
    {
        $model = Model::getContentArticle();
        if (method_exists($model, 'getItem')) {
            return $model->getItem($id);
        }
        return new stdClass();
    }

    public static function getArticleByMenuId(int $id): stdClass
    {
        $menu = Menu::getMenuById($id);
        if ($menu->id > 0) {
            $parsedStr = [];
            parse_str($menu->link, $parsedStr);
            if (isset($parsedStr['id'])) {
                return self::getArticleById($parsedStr['id']);
            }
        }
        return new stdClass();
    }
}