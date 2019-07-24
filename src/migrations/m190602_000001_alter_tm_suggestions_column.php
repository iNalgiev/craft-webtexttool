<?php
/**
 * Textmetrics plugin for Craft CMS
 *
 * @author    Israpil Nalgiev
 * @link      https://www.textmetrics.com/
 * @copyright Copyright (c) 2019 Textmetrics
 */

namespace inalgiev\webtexttool\migrations;

use craft\db\Migration;

class m190602_000001_alter_tm_suggestions_column extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $table = '{{%webtexttool_core}}';
        if($this->db->tableExists($table)) {
            $this->alterColumn($table, 'wttContentQualitySuggestions', $this->mediumText());
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m190602_000001_alter_tm_suggestions_column cannot be reverted.\n";
        return false;
    }
}