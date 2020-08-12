<?php

namespace tsmd\base\models;

use Yii;
use yii\helpers\FileHelper;

trait CodeceptBaseTrait
{
    /**
     * 用户认证信息保存路径
     *
     * @return string
     */
    protected function getSavePath()
    {
        return Yii::getAlias('@runtime/codeception');
    }

    /**
     * 保存用户认证信息
     *
     * @param $role
     * @param array $auth
     */
    protected function saveAuth($role, array $auth)
    {
        $path = $this->getSavePath();
        FileHelper::createDirectory($path);
        file_put_contents("{$path}/userAuth{$role}.txt", serialize($auth));
    }

    /**
     * 获取用户认证信息
     *
     * @param $role
     * @return mixed
     */
    protected function getAuth($role)
    {
        $path = $this->getSavePath();
        $res = file_get_contents("{$path}/userAuth{$role}.txt");
        return unserialize($res);
    }

    /**
     * 组装用户认证链接
     *
     * @param $url
     * @param $role
     * @param $params
     * @return string
     */
    protected function getAuthUrl($url, $role, $params = [])
    {
        $auth = $this->getAuth($role);
        $query = http_build_query(array_merge($auth, $params));
        return "{$url}?$query";
    }
}
