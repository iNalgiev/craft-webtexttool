<?php

namespace inalgiev\webtexttool\models;

use craft\base\Model;

/**
 * Webtexttool Core Model
 *
 * @inheritdoc
 */
class CoreModel extends Model
{
     /**
     * @var integer The entry id
     */
    public $entryId;

    /**
     * @var string The keyword the page is optimized
     */
    public $wttKeywords;

    /**
     * @var string The meta description field
     */
    public $wttDescription;

    /**
     * @var string The suggestions language
     */
    public $wttLanguage;

    /**
     * @var string The supporting keywords
     */
    public $wttSynonyms;

    /**
     * @var string The Content Quality settings
     */
    public $wttContentQualitySettings;

    /**
     * @var string The Content Quality suggestions
     */
    public $wttContentQualitySuggestions;

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            ['entryId', 'integer'],
            ['wttKeywords', 'string'],
            ['wttDescription', 'string'],
            ['wttLanguage', 'string'],
            ['wttSynonyms', 'string'],
            ['wttContentQualitySettings', 'string'],
            ['wttContentQualitySuggestions', 'string'],
        ];
    }

    /**
     * Populates a new model instance with a given set of attributes.
     *
     * @param mixed $values
     *
     * @return CoreModel
     */
    public static function populateModel($values)
    {
        $class = get_called_class();
        return new $class($values);
    }
}
