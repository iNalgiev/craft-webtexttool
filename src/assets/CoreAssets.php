<?php
/**
 * Webtexttool plugin for Craft CMS
 *
 * @author    Israpil Nalgiev
 * @link      https://www.webtexttool.com/
 * @copyright Copyright (c) 2018 Israpil Nalgiev
 */
namespace inalgiev\webtexttool\assets;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * Class SettingsAssets
 * @since 1.0.0
 */
class CoreAssets extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->sourcePath = '@inalgiev/webtexttool/resources';
        $this->depends = [CpAsset::class];
        $this->css = [
            'css/font-awesome.min.css',
            'css/angular-busy.min.css',
            'css/angular-toastr.min.css',
            'css/ladda-themeless.min.css',
            'css/flag.min.css',
            'css/ng-tags-input.css',
            'css/scrollable-table.min.css',
            'css/wtt-core.css',
        ];
        $this->js = [
            'js/getHtmlContent.js',
            'js/wtt-core.min.js',
            'js/edit-page-controller.js',
        ];
    }
}