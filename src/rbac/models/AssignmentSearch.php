<?php

namespace common\models\rbac;

use Yii;
use yii\data\ActiveDataProvider;
use common\models\User;

/**
 * AssignmentSearch represents the model behind the search form about Assignment.
 * 
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class AssignmentSearch extends \yii\base\Model
{
    public $id;
    public $nickname;
    public $cellphone;
    public $email;
    public $status;
    public $role;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'nickname', 'cellphone', 'email', 'status', 'role'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'User ID'),
            'nickname' => Yii::t('app', 'Nickname'),
            'cellphone' => Yii::t('app', 'Cellphone'),
            'email' => Yii::t('app', 'Email'),
            'status' => Yii::t('app', 'Status'),
            'role' => Yii::t('app', 'Role'),
        ];
    }

    /**
     * Create data provider for Assignment model.
     * @param  array $params
     * @param null $formName
     * @return ActiveDataProvider
     */
    public function search($params, $formName = null)
    {
        $query = User::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
        ]);

        if (!($this->load($params, $formName) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere(['id' => $this->id])
            ->andFilterWhere(['nickname' => $this->nickname])
            ->andFilterWhere(['cellphone' => $this->cellphone])
            ->andFilterWhere(['email' => $this->email])
            ->andFilterWhere(['status' => $this->status])
            ->andFilterWhere(['role' => $this->role]);

        return $dataProvider;
    }
}
