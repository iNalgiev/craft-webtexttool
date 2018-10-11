<?php
/**
 * Webtexttool plugin for Craft CMS
 *
 * @author    Israpil Nalgiev
 * @link      https://www.webtexttool.com/
 * @copyright Copyright (c) 2018 Israpil Nalgiev
 */
namespace inalgiev\webtexttool\assets;

use inalgiev\webtexttool\Webtexttool;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;
use craft\helpers\Json;
use craft\web\View;


/**
 * Class SettingsAssets
 * @since 1.0.0
 */
class DashboardAssets extends AssetBundle
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
            'css/ladda-themeless.min.css',
            'css/wtt-admin.css',
        ];
        $this->js = [
            'js/wtt-admin.min.js',
            'js/app-controller.js',
        ];
    }

    public function registerAssetFiles($view) {
        parent::registerAssetFiles($view);

        $craftJson = Json::encode($this->_wttData(), JSON_UNESCAPED_UNICODE);
        $js = <<<JS
 var wtt_dashboard = {$craftJson};
JS;
        $view->registerJs($js, View::POS_HEAD);
    }
    
    private function _wttData(): array {
        $currentUser = \Craft::$app->getUser();
        $wttConfig = \Craft::$app->getConfig()->getConfigFromFile('webtexttool');
        $userData = Webtexttool::getInstance()->UserService->getUserData($currentUser->id);

        $data = [
            'accountTemplate' => \Craft::$app->getView()->renderTemplate('webtexttool/account'),
            'loginTemplate' => \Craft::$app->getView()->renderTemplate('webtexttool/login'),
            'wttApiBaseUrl' => "https://api.webtexttool.com/",
            'currentUserId' => $currentUser->id,
            'userData' => $userData,
            'wttApiKey' => $wttConfig ? $wttConfig['wttApiKey'] : '',
        ];

        return $data;
    }
}