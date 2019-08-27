<?php
/**
 * Textmetrics plugin for Craft CMS 3
 *
 * Textmetrics is the easiest way to make your website content SEO proof, resulting in higher search engine rankings
 * and more traffic to your website. With Textmetrics everyone can create great content
 * and make sure it's SEO proof at the same time.
 *
 * @link      https://textmetrics.com
 * @copyright Copyright (c) 2019 Textmetrics
 */

namespace inalgiev\webtexttool;

use Craft;
use craft\base\Element;
use craft\base\Plugin;
use craft\events\ElementEvent;
use craft\services\Elements;
use craft\elements\Entry;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\View;
use craft\helpers\Json;

use inalgiev\webtexttool\assets\DashboardAssets;
use inalgiev\webtexttool\assets\CoreAssets;
use inalgiev\webtexttool\services\UserService;
use inalgiev\webtexttool\services\CoreService;
use inalgiev\webtexttool\models\Settings;

use yii\base\Event;

/**
 * Craft plugins are very much like little applications in and of themselves. We’ve made
 * it as simple as we can, but the training wheels are off. A little prior knowledge is
 * going to be required to write a plugin.
 *
 * For the purposes of the plugin docs, we’re going to assume that you know PHP and SQL,
 * as well as some semi-advanced concepts like object-oriented programming and PHP namespaces.
 *
 * https://craftcms.com/docs/plugins/introduction
 *
 * @author    Israpil Nalgiev
 * @package   Webtexttool
 * @since     1.0.0
 *
 */
class Webtexttool extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * Webtexttool::$plugin
     *
     * @var Webtexttool
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * To execute your plugin’s migrations, you’ll need to increase its schema version.
     *
     * @var string
     */
    public $schemaVersion = '1.0.3';

    public $hasCpSettings = false;

    public $hasCpSection = true;

    // Public Methods
    // =========================================================================

    /**
     * Set our $plugin static property to this class so that it can be accessed via
     * Webtexttool::$plugin
     *
     * Called after the plugin class is instantiated; do any one-time initialization
     * here such as hooks and events.
     *
     * If you have a '/vendor/autoload.php' file, it will be loaded for you automatically;
     * you do not need to load it in your init() method.
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();

        self::$plugin = $this;
        self::$plugin->name = 'Textmetrics';

        $this->setComponents([
            'UserService' => UserService::class,
            'CoreService' => CoreService::class,
        ]);

        // Do something after we're installed
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                    // We were just installed
                }
            }
        );

        // If not control panel request, bail
        if (!Craft::$app->getRequest()->getIsCpRequest()) {
            return false;
        }

        if (Craft::$app->getRequest()->getPathInfo() == "webtexttool") {
            $view = Craft::$app->getView();
            $view->registerAssetBundle(DashboardAssets::class);

            return $view;
        };

        Craft::$app->getView()->hook('cp.entries.edit.details', function (&$context) {
            $view = Craft::$app->getView();
            $view->registerAssetBundle(CoreAssets::class);

            $entry = $context['entry'];
            $entryId = $entry->id;

            $craftJson = Json::encode($this->_wttData($entry), JSON_UNESCAPED_UNICODE);
            $js = <<<JS
 var wtt_globals = {$craftJson};
JS;
            $view->registerJs($js, View::POS_HEAD);

            $wttCoreData = $entryId ? Webtexttool::getInstance()->CoreService->getCoreDataByEntryId($entryId) : "";

            return $view->renderTemplate('webtexttool/core', ['record' => $wttCoreData]);
        });

        //Hook into Elements save
        Event::on(
            \craft\services\Elements::class,
            \craft\services\Elements::EVENT_AFTER_SAVE_ELEMENT, function(\craft\events\ElementEvent $event) {
                $element = $event->element;

            $isRevision = false;
            $isDraft = false;

            //check if methods exists (in case Craft version < 3.2)
            if(method_exists($element, 'getIsRevision')) {
                $isRevision = $element->getIsRevision();
            }

            if(method_exists($element, 'getIsDraft')) {
                $isDraft = $element->getIsDraft();
            }

            if(
                $element instanceof \craft\elements\Entry //is entry
                //&& !\craft\helpers\ElementHelper::isDraftOrRevision($event->element) //@since Craft 3.2
                && !$isRevision // is not revision
                && !$isDraft // is not draft
            ) {
                //fired if is entry and not a revision or draft
                Craft::$app->runAction('webtexttool/core/save-record', array('attr' => $element));
            }
        }
        );
    }

    private function _wttData(Element $entry = null): array {
        $currentUser = Craft::$app->getUser();
        $wttConfig = Craft::$app->getConfig()->getConfigFromFile('webtexttool');
        $userData = Webtexttool::getInstance()->UserService->getUserData($currentUser->id);
        $record = $entry->id ? Webtexttool::getInstance()->CoreService->getCoreData($entry->id) : "";

        $data = [
            'entryId' => $entry->id,
            'record' => $record,
            'synonyms' => Json::decode($record ? $record->wttSynonyms : ""),
            'wttApiBaseUrl' => "https://api.textmetrics.com/",
            'locale' => Craft::$app->getLocale()->id,
            'userData' => $userData,
            'wttApiKey' => $wttConfig ? $wttConfig['wttApiKey'] : '',
            'permaLink' => $entry ? $entry->getUrl() : "",
            'status' => $entry->getStatus(),
            'suggestionTemplate' => Craft::$app->getView()->renderTemplate('webtexttool/directives/wtt-suggestion'),
            'suggestionInfo' => Craft::$app->getView()->renderTemplate('webtexttool/directives/wtt-suggestion-info'),
            'contentQualityTemplate' => Craft::$app->getView()->renderTemplate('webtexttool/directives/wtt-content-quality'),
            'suggestionContentQualityTemplate' => Craft::$app->getView()->renderTemplate('webtexttool/directives/wtt-suggestion-content-quality'),
            'pageSlideOut' => Craft::$app->getView()->renderTemplate('webtexttool/directives/wtt-page-slideout'),
            'craft_version' => version_compare(Craft::$app->getVersion(), '3.2.0', '>')
        ];

        return $data;
    }

    /**
     */
    public function handleEntrySave()
    {
        //Craft::$app->runAction('webtexttool/core/save-record');
    }

    /**
     * @return models\Settings Plugin settings model.
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

}