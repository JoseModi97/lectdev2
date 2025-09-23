<?php

namespace app\components;

use Yii;
use yii\helpers\Html;
use yii\helpers\Url;

class BreadcrumbHelper
{
    /**
     * Generate corporate styled breadcrumbs
     * 
     * @param array $items Array of breadcrumb items
     * @param array $options HTML options for the breadcrumb container
     * @return string HTML breadcrumb markup
     */
    public static function generate($items = [], $options = [])
    {
        if (empty($items)) {
            return '';
        }

        // Default options with corporate styling
        $defaultOptions = [
            'class' => 'breadcrumb-corporate',
            'tag' => 'ol',
            'itemTag' => 'li',
            'separator' => '',
            'homeText' => 'Home',
            'homeUrl' => ['/'],
            'encodeLabels' => true,
        ];

        $options = array_merge($defaultOptions, $options);

        // Extract non-HTML options
        $tag = $options['tag'];
        $itemTag = $options['itemTag'];
        $separator = $options['separator'];
        $homeText = $options['homeText'];
        $homeUrl = $options['homeUrl'];
        $encodeLabels = $options['encodeLabels'];

        // Remove non-HTML options from the options array
        unset(
            $options['tag'],
            $options['itemTag'],
            $options['separator'],
            $options['homeText'],
            $options['homeUrl'],
            $options['encodeLabels']
        );

        $breadcrumbItems = [];

        // Add home breadcrumb if not explicitly disabled
        if ($homeText !== false && $homeUrl !== false) {
            $breadcrumbItems[] = Html::tag(
                $itemTag,
                Html::a($encodeLabels ? Html::encode($homeText) : $homeText, $homeUrl),
                ['class' => 'breadcrumb-item']
            );
        }

        // Process each breadcrumb item
        foreach ($items as $key => $item) {
            $isLast = ($key === count($items) - 1);
            $itemOptions = ['class' => 'breadcrumb-item'];

            if ($isLast) {
                $itemOptions['class'] .= ' active';
            }

            if (is_string($item)) {
                // Simple string item
                $label = $encodeLabels ? Html::encode($item) : $item;
                $breadcrumbItems[] = Html::tag($itemTag, $label, $itemOptions);
            } elseif (is_array($item)) {
                // Array item with label and optional URL
                $label = isset($item['label']) ? $item['label'] : '';
                $url = isset($item['url']) ? $item['url'] : null;

                if ($encodeLabels) {
                    $label = Html::encode($label);
                }

                if ($url !== null && !$isLast) {
                    // Create link for non-last items with URL
                    $content = Html::a($label, $url);
                } else {
                    // Plain text for last item or items without URL
                    $content = $label;
                }

                $breadcrumbItems[] = Html::tag($itemTag, $content, $itemOptions);
            }
        }

        // Include CSS if not already included
        self::registerCss();

        return Html::tag($tag, implode('', $breadcrumbItems), $options);
    }

    /**
     * Register corporate breadcrumb CSS
     */
    protected static function registerCss()
    {
        static $registered = false;

        if (!$registered && isset(Yii::$app->view)) {
            $css = self::getCorporateCss();
            Yii::$app->view->registerCss($css);
            $registered = true;
        }
    }

    /**
     * Get corporate CSS styles
     */
    protected static function getCorporateCss()
    {

        return '
        .breadcrumb-corporate {
            background: #ffffff;
            border-left: 4px solid #0066cc;
            border-radius: 0 8px 8px 0;
            list-style: none;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        .breadcrumb-corporate .breadcrumb-item {
            display: inline-block;
            color: #4a5568;
            font-size: 13px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
        }

        .breadcrumb-corporate .breadcrumb-item:not(:last-child)::after {
            content: "▸";
            margin: 0 10px;
            color: #0066cc;
            font-weight: bold;
            font-size: 14px;
        }

        .breadcrumb-corporate .breadcrumb-item a {
            color: #0066cc;
            text-decoration: none;
            transition: all 0.3s ease;
            border-radius: 4px;
            display: inline-block;
        }

        .breadcrumb-corporate .breadcrumb-item a:hover {
            color: #004499;
            background: rgba(0, 102, 204, 0.08);
            text-decoration: none;
        }

        .breadcrumb-corporate .breadcrumb-item.active {
            color: #2d3748;
            font-weight: 700;
        }
            
        .breadcrumb-item:before{
            content: "" !important;
        }
        @media (max-width: 768px) {
            .breadcrumb-corporate {
                padding: 12px 16px;
                margin-bottom: 20px;
            }
            
            .breadcrumb-corporate .breadcrumb-item {
                font-size: 12px;
            }
            
            .breadcrumb-corporate .breadcrumb-item:not(:last-child)::after {
                margin: 0 8px;
            }
        }
        ';
    }
}

/*
Usage Examples:

// Basic usage
echo BreadcrumbHelper::generate([
    ['label' => 'Products', 'url' => ['/product/index']],
    ['label' => 'Electronics', 'url' => ['/product/category', 'id' => 1]],
    ['label' => 'Smartphones'] // Current page
]);

// With custom home text
echo BreadcrumbHelper::generate([
    ['label' => 'Dashboard', 'url' => ['/dashboard']],
    ['label' => 'Users', 'url' => ['/users']],
    ['label' => 'Edit Profile']
], [
    'homeText' => 'Dashboard',
    'homeUrl' => ['/dashboard']
]);

// Disable home breadcrumb
echo BreadcrumbHelper::generate([
    ['label' => 'Reports', 'url' => ['/reports']],
    ['label' => 'Financial', 'url' => ['/reports/financial']],
    ['label' => 'Quarterly Report']
], [
    'homeText' => false
]);

// Simple string format
echo BreadcrumbHelper::generate([
    'Products',
    'Electronics',
    'Current Page'
]);
*/