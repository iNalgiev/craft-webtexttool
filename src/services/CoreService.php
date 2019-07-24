<?php
/**
 * Textmetrics plugin for Craft CMS
 *
 * @author    Israpil Nalgiev
 * @link      https://www.textmetrics.com/
 * @copyright Copyright (c) 2019 Textmetrics
 */
namespace inalgiev\webtexttool\services;

use inalgiev\webtexttool\records\CoreRecord;
use inalgiev\webtexttool\models\CoreModel;
use yii\base\Component;
use craft\db\Query;

/**
 * Class WebtexttoolService
 * @package inalgiev\webtexttool\services
 */
class CoreService extends Component
{
    /**
     * Returns the webtexttool_core data in a CoreModel
     *
     * @param int $entryId
     * @return mixed
     */
    public function getCoreData(int $entryId)
    {
        $dataArray = (new Query())
            ->from(['{{%webtexttool_core}}'])
            ->select(['entryId', 'wttKeywords', 'wttDescription', 'wttLanguage', 'wttSynonyms', 'wttContentQualitySettings', 'wttContentQualitySuggestions'])
            ->where(['entryId' => $entryId])
            ->one();

        $coreData = CoreModel::populateModel($dataArray);

        return $coreData;
    }

    /**
     * Returns the webtexttool_core table data.
     *
     * @param int $entryId
     * @return array
     */
    public function getCoreDataByEntryId(int $entryId)
    {
        $dataArray = CoreRecord::find()->where(array('entryId' => $entryId))->one();

        return $dataArray;
    }

    /**
     * Save a new or an existing record to the database.
     *
     * @param CoreModel $coreModel
     * @return bool
     */
    public function saveRecord(CoreModel $coreModel)
    {
        if ($coreModel->validate($coreModel->entryId, true)) {
            $wttCoreRecord = CoreRecord::findOne(['entryId' => $coreModel->entryId]);

            if (!$wttCoreRecord) {
                $wttCoreRecord = new CoreRecord();
            }

            $wttCoreRecord->setAttributes($coreModel->getAttributes(), false);

            if ($wttCoreRecord->save()) {
                return true;
            } else {
                return false;
            }
        }
    }
}