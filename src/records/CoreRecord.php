<?php

namespace inalgiev\webtexttool\records;

use craft\db\ActiveRecord;

/**
 * Webtexttool Core Record
 *
 * @inheritdoc
 */
class CoreRecord extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return '{{%webtexttool_core}}';
    }
}