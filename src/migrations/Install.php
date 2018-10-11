<?php
/**
 * Webtexttool plugin for Craft CMS
 *
 * @author    Israpil Nalgiev
 * @link      https://www.webtexttool.com/
 * @copyright Copyright (c) 2018 Israpil Nalgiev
 */

namespace inalgiev\webtexttool\migrations;

use Craft;
use craft\config\DbConfig;
use craft\db\migration;

/**
 * Class Install
 * @package inalgiev\webtexttool\migrations
 */
class Install extends Migration {
    /**
     * @var string The database driver to use
     */
    public $driver;

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;

        if ($this->createTables()) {
            $this->createIndexes();
//            $this->addForeignKeys();
            // Refresh the db schema caches
            Craft::$app->db->schema->refresh();
//            $this->insertDefaultData();
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        $this->removeTables();
        return true;
    }

    /**
     * Creates the tables needed for the Records used by the plugin
     *
     * @return bool
     */
    protected function createTables()
    {
        $tablesCreated = false;

        // webtexttool_user table
        $tableSchemaUser = Craft::$app->db->schema->getTableSchema('{{%webtexttool_user}}');
        if ($tableSchemaUser === null) {
            $this->createTable(
                '{{%webtexttool_user}}',
                [
                    'id' => $this->primaryKey(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                    // Custom columns in the table
                    'userId' => $this->integer()->notNull(),
                    'accessToken' => $this->string(1024)->notNull()->defaultValue(''),
                ]
            );
        }

        // webtexttool_core table
        $tableSchemaCore = Craft::$app->db->schema->getTableSchema('{{%webtexttool_core}}');
        if ($tableSchemaCore === null) {
            $tablesCreated = true;
            $this->createTable(
                '{{%webtexttool_core}}',
                [
                    'id' => $this->primaryKey(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                    // Custom columns in the table
                    'entryId' => $this->integer()->notNull(),
                    'wttKeywords' => $this->string()->defaultValue(''),
                    'wttDescription' => $this->string(1024)->defaultValue(''),
                    'wttLanguage' => $this->string()->defaultValue(''),
                    'wttSynonyms' => $this->text(),
                    'wttContentQualitySettings' => $this->text(),
                    'wttContentQualitySuggestions' => $this->text(),
                ]
            );
        }

        return $tablesCreated;
    }

    /**
     * Creates the indexes needed for the Records used by the plugin
     *
     * @return void
     */
    protected function createIndexes()
    {
        // webtexttool_user table
        $this->createIndex(
            $this->db->getIndexName(
                '{{%webtexttool_user}}',
                ['userId'],
                true
            ),
            '{{%webtexttool_user}}',
            ['userId'],
            true
        );

        // webtexttool_core table
        $this->createIndex(
            $this->db->getIndexName('{{%webtexttool_core}}', ['entryId'], true),
            '{{%webtexttool_core}}', ['entryId'], true
        );

        // Additional commands depending on the db driver
        switch ($this->driver) {
            case DbConfig::DRIVER_MYSQL:
                break;
            case DbConfig::DRIVER_PGSQL:
                break;
        }
    }

    /**
     * Removes the tables needed for the Records used by the plugin
     *
     * @return void
     */
    protected function removeTables()
    {
        // webtexttool_user table
        $this->dropTableIfExists('{{%webtexttool_user}}');
    }
}