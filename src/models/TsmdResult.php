<?php

namespace tsmd\base\models;

use Yii;
use yii\base\InvalidArgumentException;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * @package tsmd\base\models
 */
class TsmdResult
{
    const KEY = 'tsmdResult';
    const SUC = 'SUCCESS';
    const ERR = 'ERROR';

    /**
     * @var integer
     */
    public $code;
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $type;
    /**
     * @var string
     */
    public $message;
    /**
     * @var string
     */
    public $model;
    /**
     * @var string
     */
    public $list;
    /**
     * @var string
     */
    public $listInfo;

    /**
     * @param string $sucErr eg: SUCCESS, ERROR
     * @param integer $code eg: 200, 401, 403
     * @param string $name
     * @param string $type 0 message, 1 model, 2 list
     * @param string|array $data
     * @param array $listInfo
     * @return array
     */
    public function toArray($sucErr, $code, $name, $type, &$data, &$listInfo = [])
    {
        $types = ['message', 'model', 'list'];
        $type = $types[$type] ?? $type;

        if (!in_array($type, ['message', 'model', 'list'])) {
            throw new InvalidArgumentException('Argument "type" can only be set to "message", "model" or "list".');
        }

        $this->code     = (integer) $code;
        $this->name     = (string) $name;
        $this->type     = (string) $type;
        $this->message  = '';
        $this->model    = new \stdClass();
        $this->list     = [];
        $this->listInfo = new \stdClass();

        switch ($this->type) {
            case 'message':
                if ($data === null) {
                    $data = $sucErr == self::SUC ? self::SUC : self::ERR;
                }
                $this->message = (string) $data;
                break;

            case 'model':
                if ($data instanceof Model) {
                    $this->model = $data->toArray();
                } elseif ($data) {
                    $this->model = $data;
                } else {
                    $this->model = new \stdClass();
                }
                break;

            case 'list':
                $this->list = $data;
                $this->listInfo = array_merge([
                    'count' => count($data),
                    'page' => Yii::$app->request->getPage(),
                    'pageSize' => Yii::$app->request->getPageSize(),
                    'timestamp' => time(),
                    'datec' => date('c'),
                ], $listInfo);
                break;
        }
        return [
            self::KEY  => $sucErr,
            'code'     => $this->code,
            'name'     => $this->name,
            'type'     => $this->type,
            'message'  => $this->message,
            'model'    => $this->model,
            'list'     => $this->list,
            'listInfo' => $this->listInfo,
        ];
    }

    /**
     * @param string $type
     * @param mixed $data
     * @param string $name
     * @param array $listInfo
     * @return array
     */
    public static function formatSuc($type = 'message', $data = null, $name = '', $listInfo = [])
    {
        return (new static)->toArray(self::SUC, 200, $name, $type, $data, $listInfo);
    }

    /**
     * @param mixed $data
     * @param string $name
     * @param integer $code
     * @return array
     */
    public static function formatErr($data = null, $name = '', $code = 200)
    {
        if (ArrayHelper::isAssociative($data)) {
            foreach ($data as $k => $v) {
                $name = $k;
                $data = $v;
                break;
            }
        } elseif (is_array($data)) {
            if (is_string($data[0])) {
                $data = $data[0];
            } elseif (ArrayHelper::isAssociative($data[0])) {
                foreach ($data[0] as $k => $v) {
                    $name = $k;
                    $data = $v;
                    break;
                }
            }
        }
        return (new static)->toArray(self::ERR, $code, $name, 'message', $data);
    }
}
