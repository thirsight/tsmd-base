<?php

namespace tsmd\base\rbac\models;

use Yii;
use yii\rbac\Item;
use yii\data\ArrayDataProvider;

/**
 * AuthItemSearch represents the model behind the search form about AuthItem.
 * 
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class AuthItemSearch extends AuthItem
{
    const TYPE_ROUTE = 101;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type'], 'integer'],
            [['name', 'description'], 'trim'],
            [['name', 'description'], 'string'],
        ];
    }

    /**
     * Search authitem
     * @param array $params
     * @param string $formName
     * @return \yii\data\ArrayDataProvider
     */
    public function search($params, $formName = '')
    {
        $authManager = Yii::$app->authManager;
        if ($this->type == Item::TYPE_ROLE) {
            $items = $authManager->getRoles();
        } else {
            $items = [];
            if ($this->type == Item::TYPE_PERMISSION) {
                foreach ($authManager->getPermissions() as $name => $item) {
                    if ($name[0] !== '/') {
                        $items[$name] = $item;
                    }
                }
            } else {
                foreach ($authManager->getPermissions() as $name => $item) {
                    if ($name[0] === '/') {
                        $items[$name] = $item;
                    }
                }
            }
        }

        if ($this->load($params, $formName) && $this->validate() && ($this->name !== '' || $this->description !== '')) {
            $name = strtolower($this->name);
            $desc = strtolower($this->description);
            $items = array_filter($items, function ($item) use ($name, $desc) {
                return (empty($name) || strpos(strtolower($item->name), $name) !== false) && (empty($desc) || strpos(strtolower($item->description), $desc) !== false);
            });
        }

        return new ArrayDataProvider([
            'allModels' => $items,
        ]);
    }
}
