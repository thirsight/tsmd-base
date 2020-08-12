<?php

namespace tsmd\base\yii;

/**
 * @author Haisen <thirsight@gmail.com>
 * @since 1.0
 */
class YiiRequest extends \yii\web\Request
{
    /**
     * @inheritdoc
     */
    public function getUserIP()
    {
        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
            $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        } elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            if (stripos($_SERVER["HTTP_X_FORWARDED_FOR"], ',') !== false) {
                $_SERVER['REMOTE_ADDR'] = explode(',', $_SERVER["HTTP_X_FORWARDED_FOR"])[0];
            } else {
                $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_X_FORWARDED_FOR"];
            }
        }
        $ip = parent::getUserIP();
        return $ip ?: (!YII_ENV_PROD ? '127.0.0.1' : null);
    }

    /**
     * @param null $name
     * @param null $defaultValue
     * @return array|mixed
     */
    public function getPost($name = null, $defaultValue = null)
    {
        return $this->get($name, $defaultValue) ?: $this->post($name, $defaultValue);
    }

    /**
     * 获取分页参数，第几页，最多10页
     *
     * @return integer
     */
    public function getPage()
    {
        $page = $this->getQueryParam('page', 1);
        return min(max($page, 1), 100);
    }

    /**
     * 获取分页参数，每页记录数量，最多100条，用于查询参数 limit
     *
     * @param int $defaultValue
     * @return integer
     */
    public function getPageSize($defaultValue = 20)
    {
        $pageSize = $this->getQueryParam('pageSize', $defaultValue);
        return min(max($pageSize, -1), 1000);
    }

    /**
     * 获取分页参数，每页记录起始值，用户查询参数 offset
     *
     * @return integer
     */
    public function getPageOffset()
    {
        return ($this->getPage() - 1) * $this->getPageSize();
    }
}
