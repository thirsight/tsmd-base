<?php

namespace tsmd\base\user\models;

use Yii;

/**
 * UsersSearch represents the model behind the search form about `User`.
 */
class UserSearch extends User
{
    /**
     * @var string
     */
    public $s;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['s', 'string'],

            ['uid', 'integer'],

            ['alpha2', 'string'],

            ['cellphone', 'string'],

            ['email', 'email'],

            ['status', 'in', 'range' => array_keys(static::presetStatuses()), 'when' => function ($model) {
                return is_string($model->status);
            }],

            ['role', 'in', 'range' => array_keys(static::presetRoles()), 'when' => function ($model) {
                return is_string($model->role);
            }],

            ['username', 'string'],
            ['realname', 'string'],
            ['nickname', 'string'],

            ['gender', 'in', 'range' => array_keys(static::presetGenders())],
        ];
    }

    /**
     * @param array $params
     * @return array|null
     */
    public function search($params)
    {
        $this->load($params, '');
        if (!$this->validate()) {
            return [];
        }

        if (stripos($this->s, '*') !== false) {
            $this->s = str_ireplace('*', '%', $this->s);
            $sLike = ['or',
                ['like', 'cellphone', $this->s, false],
                ['like', 'email', $this->s, false],
                ['like', 'username', $this->s, false],
            ];

        } elseif (preg_match('#^(?:1\d{10}|09\d{8})$#', $this->s)) {
            $this->cellphone = $this->s;

        } elseif (is_numeric($this->s)) {
            $this->uid = $this->s;

        } elseif (stripos($this->s, '@') !== false) {
            $this->email = $this->s;

        } elseif ($this->s) {
            $this->username = $this->s;
        }

        return static::query()
            ->select('uid, alpha2, cellphone, email, status, role, username')
            ->addSelect('realname, nickname, gender, slug, createdAt, updatedAt')
            ->andFilterWhere([
                'uid'       => $this->uid,
                'alpha2'    => $this->alpha2,
                'cellphone' => $this->cellphone,
                'email'     => $this->email,
                'username'  => $this->username,
                'realname'  => $this->realname,
                'nickname'  => $this->nickname,
                'gender'    => $this->gender,
            ])

            ->andFilterWhere(['in', 'status', $this->status])
            ->andFilterWhere(['in', 'role', $this->role])

            ->andFilterWhere($sLike ?? [])

            ->offset(Yii::$app->request->getPageOffset())
            ->limit(Yii::$app->request->getPageSize())
            ->orderBy('uid DESC')
            ->all();
    }
}
