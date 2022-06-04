<?php

namespace tsmd\base\models;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * @package tsmd\base\models
 */
class TsmdResult
{
    const SUC = 'SUCCESS';
    const ERR = 'ERROR';

    /**
     * @var string
     */
    private $tsmdResult = '';
    /**
     * @var integer
     */
    private $code = 200;
    /**
     * @var string
     */
    private $name = '';
    /**
     * @var string
     */
    private $type = '';
    /**
     * @var string
     */
    private $message = '';
    /**
     * @var array
     */
    private $model = [];
    /**
     * @var array
     */
    private $list = [];
    /**
     * @var array
     */
    private $listInfo = [];
    /**
     * @var array
     */
    private $params = [];

    /**
     * @param bool $bool
     */
    public function setTsmdResult(bool $bool)
    {
        $this->tsmdResult = $bool ? static::SUC : static::ERR;
        $this->message = $this->tsmdResult;
    }

    /**
     * @param int $code
     */
    public function setCode(int $code)
    {
        $this->code = (int) $code;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = (string) $name;
    }

    /**
     * @param string $type
     */
    public function setType(string $type)
    {
        $this->type = (string) $type;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message)
    {
        $this->type = 'message';
        $this->message = (string) $message;
    }

    /**
     * @param array|Model $model
     */
    public function setModel($model)
    {
        $this->type = 'model';
        if ($model instanceof Model) {
            $this->model = $model->toArray();
        } elseif ($model) {
            $this->model = $model;
        } else {
            $this->model = new \stdClass();
        }
    }

    /**
     * @param array $list
     */
    public function setList(array $list)
    {
        $this->type = 'list';
        $this->list = $list;
        $this->listInfo = [
            'count' => count($list),
            'page' => Yii::$app->request->getPage(),
            'pageSize' => Yii::$app->request->getPageSize(),
            'timestamp' => time(),
            'datec' => date('c'),
        ];
    }

    /**
     * @param array $listInfo
     */
    public function setListInfo(array $listInfo)
    {
        $this->listInfo = array_merge($this->listInfo ?: [], $listInfo);
    }

    /**
     * @param string $key
     * @param string $value
     */
    public function setParams(string $key, string $value)
    {
        $this->params[] = ['key' => $key, 'value' => $value];
    }

    /**
     * @return array
     */
    public function toArray()
    {
        if (is_array($this->model) && empty($this->model)) {
            $this->model = new \stdClass();
        }
        return [
            'tsmdResult' => $this->tsmdResult,
            'code'       => $this->code,
            'name'       => $this->name,
            'type'       => $this->type,
            'message'    => $this->message,
            'model'      => $this->model,
            'list'       => $this->list,
            'listInfo'   => $this->listInfo,
            'params'     => $this->params,
        ];
    }

    /**
     * @param mixed $data
     * @param array $listInfo
     * @param string $name
     * @return array
     */
    public static function response($data = '', $listInfo = [], $name = '')
    {
        $self = new static();
        $self->setTsmdResult(true);
        $self->setName($name);

        if (is_string($data)) {
            $self->setMessage($data ?: static::SUC);

        } elseif (is_array($data) && (isset($data[0]) || empty($data))) {
            $self->setList($data);
            $self->setListInfo($listInfo);

        } else {
            $self->setModel($data);
        }
        return $self->toArray();
    }

    /**
     * @param mixed $model
     * @param string $name
     * @return array
     */
    public static function responseModel($model, $name = '')
    {
        $self = new static();
        $self->setTsmdResult(true);
        $self->setName($name);
        $self->setModel($model);
        return $self->toArray();
    }

    /**
     * @param mixed $data
     * @param array $kvs ['key' => 'value', ...]
     * @param string $name
     * @param integer $code
     * @return array
     */
    public static function failed($data = null, $kvs = [], $name = '', $code = 200)
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
        } elseif (empty($data)) {
            $data = static::ERR;
        }
        $self = new static();
        $self->setTsmdResult(false);
        $self->setCode($code);
        $self->setName($name);
        $self->setMessage($data);

        foreach ($kvs as $k => $v) {
            $self->setParams($k, $v);
        }
        return $self->toArray();
    }
}
