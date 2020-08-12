<?php

namespace tsmd\base\rbac\models;

use Yii;
use yii\rbac\Item;

/**
 * This is the model class for table "auth_item".
 *
 * @property integer $type
 * @property string $name
 * @property string $description
 * @property string $ruleName
 * @property string $data
 *
 * @property Item $item
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class AuthItem extends \yii\base\Model
{
    /**
     * @var integer the type of the item. This should be either [[TYPE_ROLE]] or [[TYPE_PERMISSION]].
     */
    public $type;
    /**
     * @var string the old name of the item.
     */
    public $oldName;
    /**
     * @var string the name of the item. This must be globally unique.
     */
    public $name;
    /**
     * @var string the item description
     */
    public $description;
    /**
     * @var string name of the rule associated with this item
     */
    public $ruleName;
    /**
     * @var mixed the additional data associated with this item
     */
    public $data;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'type' => Yii::t('base', 'Type'),
            'oldName' => Yii::t('base', 'Old Name'),
            'name' => Yii::t('base', 'Name'),
            'description' => Yii::t('base', 'Description'),
            'ruleName' => Yii::t('base', 'Rule Name'),
            'data' => Yii::t('base', 'Data'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'create' => ['type', 'name', 'description', 'ruleName', 'data'],
            'update' => ['type', 'oldName', 'name', 'description', 'ruleName', 'data'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['type', 'required'],
            ['type', 'in', 'range' => [Item::TYPE_ROLE, Item::TYPE_PERMISSION]],

            ['oldName', 'trim'],
            ['oldName', 'required'],
            ['oldName', 'string', 'max' => 64],

            ['name', 'trim'],
            ['name', 'required'],
            ['name', 'string', 'max' => 64],
            ['name', function ($attribute, $params) {
                if ($this->oldName != $this->name) {
                    if (static::findOne($this->name, $this->type)) {
                        $this->addError($attribute, "'Name' is already exist.");
                    }
                }
            }],

            [['description', 'ruleName', 'data'], 'trim'],
            [['description', 'ruleName', 'data'], 'default'],
        ];
    }

    /**
     * Get type name
     * @param  mixed $type
     * @return string|array
     */
    public static function presetTypes($type = null)
    {
        $result = [
            Item::TYPE_ROLE => 'Role',
            Item::TYPE_PERMISSION => 'Permission',
        ];
        if ($type === null) {
            return $result;
        }
        return $result[$type];
    }

    /**
     * @param $name
     * @param $type
     * @return null|\yii\rbac\Permission|\yii\rbac\Role
     */
    public static function findOne($name, $type)
    {
        if ($type == Item::TYPE_ROLE) {
            return Yii::$app->authManager->getRole($name);

        } elseif ($type == Item::TYPE_PERMISSION) {
            return Yii::$app->authManager->getPermission($name);
        }
        return null;
    }

    /**
     * @param $data
     * @return AuthItem
     */
    public static function createBy($data)
    {
        $model = new self();
        $model->scenario = 'create';
        $model->load($data, '');

        if ($model->validate()) {
            $item = new \yii\rbac\Item($model->toArray(['type', 'name', 'description', 'ruleName', 'data']));
            Yii::$app->authManager->add($item);
        }
        return $model;
    }

    /**
     * @param $data
     * @return AuthItem
     */
    public static function updateBy($data)
    {
        $model = new self();
        $model->scenario = 'update';
        $model->load($data, '');

        if ($model->validate()) {
            $item = new \yii\rbac\Item($model->toArray(['type', 'name', 'description', 'ruleName', 'data']));
            Yii::$app->authManager->update($model->oldName, $item);
        }
        return $model;
    }
}
