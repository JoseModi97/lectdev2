<?php

/**
 * Menu Builder for JSTree
 * User: Jack
 * Time: 4:25 PM
 */

namespace app\components;

use yii\helpers\Html;
use yii\helpers\Url;

class Menu
{
    private $menuItems = [];

    /**
     * Create a menu item
     * @param string $text
     * @param string|null $href
     * @param string $icon
     * @param array $children
     * @param array $options
     * @return array
     */
    public static function item($text, $href = null, $icon = '', $children = [], $options = [])
    {
        $item = [
            'text' => $text,
            'a_attr' => ['href' => $href ?: 'javascript:void(0)']
        ];

        if (!empty($icon)) {
            $item['icon'] = $icon;
        }

        if (!empty($children)) {
            $item['children'] = $children;
        }

        if ($href && $href !== 'javascript:void(0)') {
            $item['type'] = 'links';
        }

        $item = array_merge($item, $options);

        return $item;
    }

    /**
     * Create a parent menu with bold text
     * @param string $name
     * @param array $children
     * @param array $options
     * @return array
     */
    public static function parent($name, $children = [], $options = [])
    {
        $boldText = '<span style="font-weight:bold;">' . Html::encode($name) . '</span>';
        return self::item($boldText, null, '', $children, $options);
    }

    /**
     * Create a link menu item
     * @param string $name
     * @param string|array $link
     * @param string $icon
     * @param array $options
     * @return array
     */
    public static function link($name, $link, $icon = '', $options = [])
    {
        $href = is_array($link) ? Url::to($link) : $link;
        return self::item($name, $href, $icon, [], $options);
    }

    /**
     * Create a reports menu with default icon
     * @param array $children
     * @param array $options
     * @return array
     */
    public static function reports($children = [], $options = [])
    {
        $defaultOptions = [
            'state' => ['opened' => true],
            'icon' => 'fa fa-chart-bar'
        ];
        $options = array_merge($defaultOptions, $options);

        return self::item('Reports', null, $options['icon'], $children, $options);
    }

    /**
     * Build a complete menu tree
     * @param array $menuData
     * @return string JSON encoded menu
     */
    public static function build($menuData)
    {
        return json_encode($menuData, JSON_UNESCAPED_SLASHES);
    }

    /**
     * Create multiple menu sections
     * @param array $sections
     * @return array
     */
    public static function sections($sections)
    {
        $result = [];
        foreach ($sections as $section) {
            $result[] = self::build([$section]);
        }
        return $result;
    }
}
