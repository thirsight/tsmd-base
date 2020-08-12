<?php

namespace tsmd\base\aws\s3;

use Yii;
use yii\base\InvalidConfigException;

/**
 * S3Cache implements a cache component using AmazonS3.
 *
 * @author Haisen <thirsight@gmail.com>
 * @since 1.0
 */
class S3Cache extends \yii\caching\Cache
{
    /**
     * @var string the directory to store cache files. You may use [path alias](guide:concept-aliases) here.
     * If not set, it will use the "cache" subdirectory under the application runtime path.
     */
    public $cachePath = 'runtime';

    /**
     * @var string cache file suffix. Defaults to '.bin'.
     */
    public $cacheFileSuffix = '.bin';

    /**
     * @var string eg: ap-northeast-2
     */
    public $region;

    /**
     * @var string eg: latest
     */
    public $version;

    /**
     * @var string eg: AKIAIR... (about 20 chars)
     */
    public $credentialKey;

    /**
     * @var string eg: U1mpM7... (about 40 chars)
     */
    public $credentialSecret;

    /**
     * @var string eg: https://s3-ap-northeast-2.amazonaws.com
     */
    public $endpoint;

    /**
     * @var string
     */
    public $bucket;

    /**
     * @var \Aws\S3\S3Client
     */
    private $_s3;

    /**
     * Initializes this component by ensuring the existence of the cache path.
     */
    public function init()
    {
        parent::init();

        if ($this->endpoint === null) {
            throw new InvalidConfigException('The parameter of "endpoint" must be set.');
        }
        if ($this->bucket === null) {
            throw new InvalidConfigException('The parameter of "bucket" must be set.');
        }

        /* @var $config \tsmd\base\aws\Config */
        $config = Yii::$app->get('AWSConfig');

        $args = [
            'endpoint' => $this->endpoint,
            'region'   => $this->region ?: $config->region,
            'version'  => $this->version ?: $config->version,
            'credentials' => [
                'key'    => $this->credentialKey ?: $config->credentialKey,
                'secret' => $this->credentialSecret ?: $config->credentialSecret,
            ]
        ];
        $this->_s3 = (new \Aws\Sdk($args))->createS3();
    }

    /**
     * Returns the cache file path given the cache key.
     * @param string $key cache key
     * @return string the cache file path
     */
    protected function getCacheFile($key)
    {
        return $this->cachePath . DIRECTORY_SEPARATOR . $key . $this->cacheFileSuffix;
    }

    /**
     * @param string $key eg: key, dir\key
     * @return string|false the value stored in cache, false if the value is not in the cache or expired.
     */
    protected function getValue($key)
    {
        try {
            $res = $this->_s3->getObject([
                'Bucket' => $this->bucket,
                'Key' => $this->getCacheFile($key),
            ]);

            /* 是否过期，服务器时间须转换为本地时间 */
            /* @var $expires \Aws\Api\DateTimeResult */
            $expires = $res['Expires'];
            $expires->setTimezone(new \DateTimeZone(date('e')));
            if ($expires->getTimestamp() < time()) {
                return false;
            }

            // 返回内容
            if ($res['Body']) {
                return $res['Body']->getContents();
            }
            return false;

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int $duration
     * @return bool
     */
    protected function setValue($key, $value, $duration)
    {
        $duration = $duration <= 0 ? 86400 * 365 * 99 : $duration;

        try {
            $this->_s3->putObject([
                'Bucket' => $this->bucket,
                'Key'  => $this->getCacheFile($key),
                'Body' => $value,
                'Expires' => date('r', time() + $duration),
            ]);
            return true;

        } catch (\Exception $e) {
            $error = error_get_last();
            Yii::warning("Unable to write cache file '{$this->getCacheFile($key)}': {$error['message']}", __METHOD__);
            return false;
        }
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int $duration
     * @return bool
     */
    protected function addValue($key, $value, $duration)
    {
        if ($this->getValue($key)) {
            return false;
        }
        return $this->setValue($key, $value, $duration);
    }

    /**
     * @param string $key
     * @return bool
     */
    protected function deleteValue($key)
    {
        try {
            $this->_s3->deleteObject([
                'Bucket' => $this->bucket,
                'Key'  => $this->getCacheFile($key),
            ]);
            return true;

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Deletes all values from cache.
     * This is the implementation of the method declared in the parent class.
     * @return bool whether the flush operation was successful.
     */
    protected function flushValues()
    {
        return false;
    }
}
