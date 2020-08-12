<?php

namespace tsmd\base\yii;

/**
 * @author Haisen <thirsight@gmail.com>
 * @since 1.0
 */
trait YiiSchemaBuilderTrait
{
    /**
     * @return \yii\db\Connection the database connection to be used for schema building.
     */
    abstract protected function getDb();

    /**
     * Creates a tinytext column.
     * @return \yii\db\ColumnSchemaBuilder the column instance which can be further customized.
     * @since 2.0.6
     */
    public function tinyText()
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder('tinytext');
    }

    /**
     * Creates a mediumtext column.
     * @return \yii\db\ColumnSchemaBuilder the column instance which can be further customized.
     * @since 2.0.6
     */
    public function mediumText()
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder('mediumtext');
    }

    /**
     * Creates a longtext column.
     * @return \yii\db\ColumnSchemaBuilder the column instance which can be further customized.
     * @since 2.0.6
     */
    public function longText()
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder('longtext');
    }
}
