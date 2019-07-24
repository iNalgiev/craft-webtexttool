<?php
/**
 * Textmetrics plugin for Craft CMS
 *
 * @author    Israpil Nalgiev
 * @link      https://www.textmetrics.com/
 * @copyright Copyright (c) 2019 Textmetrics
 */

namespace inalgiev\webtexttool\controllers;

use Craft;
use craft\web\Controller;
use craft\helpers\UrlHelper;
use inalgiev\webtexttool\Webtexttool;
use inalgiev\webtexttool\models\CoreModel;
use yii\web\Response;
use yii\base\Exception;
use yii\web\ServerErrorHttpException;

/**
 * Class WebtexttoolController
 * @package inalgiev\webtexttool\controllers
 */
class CoreController extends Controller
{
    /**
     * @return \yii\web\Response
     * @throws \yii\web\BadRequestHttpException
     * @throws Exception
     */
    public function actionGetUrlWithToken()
    {
        $this->requireAcceptsJson();
        $request = Craft::$app->getRequest();

        $params = ['entryId' => $request->getBodyParam('entryId')];
        $status = ['status' => $request->getBodyParam('status')];
        $entryUrl = $request->getBodyParam('url');

        if($status['status'] !== "live") {
            Craft::$app->getTokens()->deleteExpiredTokens();

            $token = Craft::$app->getTokens()->createToken([
                'entries/view-shared-entry',
                $params
            ]);

            if ($token === false) {
                throw new Exception('There was a problem generating the token.');
            }

            $url = UrlHelper::urlWithToken($entryUrl, $token);
        } else {
            $url = $request->getBodyParam('url');
        }

        if($request->getAcceptsJson()) {
            return $this->asJson(['url' => $url]);
        }
    }

    /**
     * @return \yii\web\Response
     * @throws \yii\web\BadRequestHttpException
     * @throws Exception
     */
    public function actionGetPreviewToken()
    {
        $request = Craft::$app->getRequest();
        $elementType = $request->getRequiredBodyParam('elementType');
        $sourceId = $request->getRequiredBodyParam('sourceId');
        $siteId = $request->getRequiredBodyParam('siteId');
        $draftId = $request->getBodyParam('draftId');
        $revisionId = $request->getBodyParam('revisionId');

        if ($draftId) {
            $this->requireAuthorization('previewDraft:' . $draftId);
        } else if ($revisionId) {
            $this->requireAuthorization('previewRevision:' . $revisionId);
        } else {
            $this->requireAuthorization('previewElement:' . $sourceId);
        }

        // Create a 24 hour token
        $route = [
            'preview/preview', [
                'elementType' => $elementType,
                'sourceId' => (int)$sourceId,
                'siteId' => (int)$siteId,
                'draftId' => (int)$draftId ?: null,
                'revisionId' => (int)$revisionId ?: null,
            ]
        ];

        $expiryDate = (new \DateTime())->add(new \DateInterval('P1D'));
        $token = Craft::$app->getTokens()->createToken($route, null, $expiryDate);

        if (!$token) {
            throw new ServerErrorHttpException(Craft::t('app', 'Could not create a preview token.'));
        }

        return $this->asJson(compact('token'));
    }

    /**
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSaveContentQualitySettings(): Response {

        $this->requireAcceptsJson();
        $request = Craft::$app->getRequest();
        $params = array('entryId' => $request->getBodyParam('entryId'), 'data' => $request->getBodyParam('data'), 'recordId' => $request->getBodyParam('recordId'));

        if($params['entryId'] && $params['recordId']) {
            $model = Webtexttool::getInstance()->CoreService->getCoreData($params['entryId']);
        } else {
            $model = new CoreModel();
        }

        $model->entryId = $params['entryId'];
        $model->wttContentQualitySettings = $params['data'];

        if(Webtexttool::getInstance()->CoreService->saveRecord($model)) {
            return $this->asJson(['success' => true]);
        };

        return $this->asJson(['success' => false, 'data' => $params['data']]);
    }

    /**
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSaveContentQualitySuggestions(): Response {
        $this->requireAcceptsJson();
        $request = Craft::$app->getRequest();
        $params = array('entryId' => $request->getBodyParam('entryId'), 'data' => $request->getBodyParam('data'), 'recordId' => $request->getBodyParam('recordId'));

        if($params['entryId'] && $params['recordId']) {
            $model = Webtexttool::getInstance()->CoreService->getCoreData($params['entryId']);
        } else {
            $model = new CoreModel();
        }

        $model->entryId = $params['entryId'];
        $model->wttContentQualitySuggestions = $params['data'];

        if(Webtexttool::getInstance()->CoreService->saveRecord($model)) {
            return $this->asJson(['success' => true]);
        };

        return $this->asJson(['success' => false]);

    }

    /**
     * Intercept the Entry save data
     *
     * @param $attr array The base class Event.
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSaveRecord($attr)
    {
        $this->requirePostRequest();
        $request = Craft::$app->getRequest();
        $entryId = $attr->sender->attributes['id'] ? $attr->sender->attributes['id'] : $request->getBodyParam('entryId');

        if ($entryId) {
            $model = Webtexttool::getInstance()->CoreService->getCoreData($entryId);
        } else {
            $model = new CoreModel();
        }

        $model->entryId = $entryId;
        $model->wttKeywords = $request->getBodyParam('wtt_keyword');
        $model->wttDescription = $request->getBodyParam('wtt_description');
        $model->wttLanguage = $request->getBodyParam('wtt_language');
        $model->wttSynonyms = $request->getBodyParam('wtt_synonym_tags');

        if ($model->validate($model->entryId, true)) {
            Webtexttool::getInstance()->CoreService->saveRecord($model);
        }
    }
}