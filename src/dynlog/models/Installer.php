<?php

namespace tsmd\base\dynlog\models;

use Yii;

/**
 * Install User module action
 */
class Installer extends \tsmd\base\models\ModuleInstaller
{
    /**
     * @inheritdoc
     */
    public function initDocs()
    {

    }

    /**
     * @return array|bool
     */
    public function initTable()
    {
        try {
            return Yii::$app->get('dynamodb')->createCommand()->createTable(DynLog::tableName(), [
                'AttributeDefinitions' => [
                    [
                        'AttributeName' => 'uid',
                        'AttributeType' => 'S'
                    ],
                    [
                        'AttributeName' => 'microtime',
                        'AttributeType' => 'N'
                    ],
                    [
                        'AttributeName' => 'object',
                        'AttributeType' => 'S'
                    ],
                ],
                'KeySchema' => [
                    [
                        'AttributeName' => 'uid',
                        'KeyType' => 'HASH', //Partition key
                    ],
                    [
                        'AttributeName' => 'microtime',
                        'KeyType' => 'RANGE', //Sort key
                    ],
                ],
                'ProvisionedThroughput' => [
                    'ReadCapacityUnits' => 5,
                    'WriteCapacityUnits' => 5,
                ],
                'GlobalSecondaryIndexes' => [
                    [
                        'IndexName' => 'object-gindex',
                        'KeySchema' => [
                            [
                                'AttributeName' => 'object',
                                'KeyType' => 'HASH',
                            ],
                            [
                                'AttributeName' => 'microtime',
                                'KeyType' => 'RANGE',
                            ]
                        ],
                        'Projection' => [
                            'ProjectionType' => 'ALL',
                        ],
                        'ProvisionedThroughput' => [
                            'ReadCapacityUnits' => 5,
                            'WriteCapacityUnits' => 5,
                        ],
                    ]
                ],
            ])->execute();

        } catch (\Aws\Exception\AwsException $e) {
            $this->_errors[$e->getCommand()->getName()] = $e->getAwsErrorMessage();

        } catch (\Exception $e) {
            $this->_errors[] = $e->getMessage();
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function initOption()
    {

    }

    /**
     * @inheritdoc
     */
    public function initRbac()
    {

    }

    /**
     * @inheritdoc
     */
    public function initMenu()
    {

    }
}
