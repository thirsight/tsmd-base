<?php

namespace tsmd\base\tests\fixtures;

use yii\test\ArrayFixture;
use tsmd\base\user\models\User;

class UsersFixture extends ArrayFixture
{
    protected $uidTags = [
        1    => 'be',
        1000 => 'fe'
    ];

    protected function getData()
    {
        static $data = [];
        if (empty($data)) {
            $users = User::find()->where(['in', 'uid', array_keys($this->uidTags)])->with('usermbr')->all(User::getDb());
            foreach ($users as $u) {
                $uidTag = $this->uidTags[$u['uid']];
                $data[$uidTag] = [
                    'uid' => $u->uid,
                    'accessToken' => $u->generateAccessToken(),
                ];
            }
        }
        return $data;
    }

    public function wrapUrl($url, $uidTag)
    {
        return "{$url}?accessToken=" . urlencode($this->data[$uidTag]['accessToken']);
    }
}