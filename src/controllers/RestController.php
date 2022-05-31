<?php

namespace tsmd\base\controllers;

use Yii;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;
use tsmd\base\user\models\User;

/**
 * @author Haisen <thirsight@gmail.com>
 * @since 1.0
 */
abstract class RestController extends \yii\rest\Controller
{
    /**
     * @var string|array 跨域请求认证
     */
    protected $corsOrigin;

    /**
     * @var array 无须 accessToken 认证的接口 (action IDs)
     */
    protected $authExcept = [];

    /**
     * @var \tsmd\base\user\models\User
     */
    protected $user;

    /**
     * @var array [0, 9] 允许认证用户角色
     */
    protected $allowUserRole = [User::ROLE_MEMBER, User::ROLE_ADMIN];

    /**
     * @var array [200, 201] 允许认证用户角色
     */
    protected $allowUserStatus = [User::STATUS_OK];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        Yii::$app->response->format = Response::FORMAT_JSON;

        $this->on(self::EVENT_BEFORE_ACTION, [$this, 'prepare']);
        $this->on(self::EVENT_AFTER_ACTION, [$this, 'calcExecTime']);
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        // params 参数于 ../params.php 中设置
        if (!$this->corsOrigin && isset(Yii::$app->params[self::class]['corsOrigin'])) {
            $this->corsOrigin = Yii::$app->params[self::class]['corsOrigin'];
        } else {
            $this->corsOrigin = ['*'];
        }
        if (stripos(Yii::$app->request->headers->get('Origin'), 'localhost:') !== false) {
            $this->corsOrigin[] = Yii::$app->request->headers->get('Origin');
        }

        return [
            'corsFilter' => [
                'class' => \yii\filters\Cors::class,
                'cors' => [
                    'Origin' => $this->corsOrigin,
                    'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                    'Access-Control-Request-Headers' => ['*'],
                    'Access-Control-Max-Age' => 86400,
                    'Access-Control-Expose-Headers' => [
                        'link',
                        'x-pagination-current-page',
                        'x-pagination-page-count',
                        'x-pagination-per-page',
                        'x-pagination-total-count',
                    ],
                ],
            ],
            'authenticator' => [
                'class' => \yii\filters\auth\CompositeAuth::class,
                'authMethods' => [
                    [
                        'class' => '\yii\filters\auth\QueryParamAuth',
                        'tokenParam' => 'accessToken',
                    ],
                    [
                        'class' => '\yii\filters\auth\HttpBearerAuth',
                        'header' => 'TSMD-Authorization',
                    ],
                ],
                'except' => $this->authExcept,
            ],
            'contentNegotiator' => [
                'class' => \yii\filters\ContentNegotiator::class,
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                    'application/xml' => Response::FORMAT_XML,
                ],
            ],
        ];
    }

    /**
     * @throws UnauthorizedHttpException
     */
    protected function prepare()
    {
        $this->user = Yii::$app->user->identity;

        if ($this->user) {
            // 判断登入用户的角色是否被允许
            if ($this->allowUserRole && !in_array($this->user->role, (array) $this->allowUserRole)) {
                throw new UnauthorizedHttpException('Your request was made with invalid credentials (role).');
            }
            // 判断登入用户状态是否被允许
            if ($this->allowUserStatus && !in_array($this->user->status, (array) $this->allowUserStatus)) {
                throw new UnauthorizedHttpException('Your request was made with invalid credentials (status).');
            }
        }
    }

    /**
     * 在 header 头增加执行时间
     */
    protected function calcExecTime()
    {
        $execTime = round(microtime(true) - YII_BEGIN_TIME, 4);
        Yii::$app->response->headers->set('tsmd-exec-time', $execTime);
        Yii::$app->response->headers->set('tsmd-env', YII_ENV);
    }

    /**
     * @var array
     */
    private $_queryParams;

    /**
     * 接口接收到的数据
     *
     * @param string $name the parameter name
     * @param mixed $defaultValue the default parameter value if the parameter does not exist.
     * @return array|string
     */
    protected function getQueryParams($name = null, $defaultValue = null)
    {
        if ($this->_queryParams === null) {
            $this->_queryParams = Yii::$app->request->queryParams;
            array_walk($this->_queryParams, function (&$val) {
                $val = is_string($val) ? Yii::$app->formatter->mergeBlank(strip_tags($val)) : $val;
            });
        }
        if ($name === null) {
            return $this->_queryParams;
        }
        return $this->_queryParams[$name] ?? $defaultValue;
    }

    /**
     * @var array
     */
    private $_bodyParams;

    /**
     * 接口接收到的数据
     *
     * @param string $name the parameter name
     * @param mixed $defaultValue the default parameter value if the parameter does not exist.
     * @return array|string
     */
    protected function getBodyParams($name = null, $defaultValue = null)
    {
        if ($this->_bodyParams === null) {
            $this->_bodyParams = Yii::$app->request->bodyParams;
            array_walk($this->_bodyParams, function (&$val) {
                $val = is_string($val) ? Yii::$app->formatter->mergeBlank(strip_tags($val)) : $val;
            });
        }
        if ($name === null) {
            return $this->_bodyParams;
        }
        return $this->_bodyParams[$name] ?? $defaultValue;
    }
}
