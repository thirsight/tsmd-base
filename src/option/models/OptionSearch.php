<?php

namespace tsmd\base\option\models;

/**
 * OptionSearch represents the model behind the search form about `tsmd\base\option\models\Option`.
 */
class OptionSearch extends Option
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['id', 'integer'],
            ['group', 'string'],
            ['key', 'string'],
            ['autoload', 'in', 'range' => [0, 1]],
        ];
    }

    /**
     * @param array $params
     * @return array
     */
    public function search($params)
    {
        $this->load($params, '');
        if (!$this->validate()) {
            return null;
        }
        $rows = Option::query()
            ->andFilterWhere(['id' => $this->id])
            ->andFilterWhere(['key' => $this->key])
            ->andFilterWhere(['group' => $this->group])
            ->andFilterWhere(['autoload' => $this->autoload])
            ->orderBy('sort ASC, id ASC')
            ->all(self::getDb());
        array_walk($rows, function (&$r) {
            Option::queryOutputBy($r);
        });
        return $rows;
    }
}
