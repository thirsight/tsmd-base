<?php

namespace tsmd\base\user\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * 用户模型
 *
 * @property integer $uid AUTO_INCREMENT 100000+
 * @property string $alpha2
 * @property string $cellphone
 * @property string $email
 * @property string $authKey
 * @property string $passwordHash
 * @property integer $status
 * @property integer $role
 * @property string $username
 * @property string $realname
 * @property string $nickname
 * @property string $slug
 * @property string $createdTime
 * @property string $updatedTime
 *
 * @property string $password write-only password
 *
 * @property Usermbr $usermbr
 */
class User extends \tsmd\base\models\ArModel implements \yii\web\IdentityInterface
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'uid'          => Yii::t('base', 'User ID'),
            'alpha2'       => Yii::t('base', 'Country'),
            'cellphone'    => Yii::t('base', 'Cellphone'),
            'email'        => Yii::t('base', 'Email'),
            'authKey'      => Yii::t('base', 'Auth Key'),
            'passwordHash' => Yii::t('base', 'Password Hash'),
            'status'       => Yii::t('base', 'Status'),
            'role'         => Yii::t('base', 'Role'),
            'username'     => Yii::t('base', 'Username'),
            'realname'     => Yii::t('base', 'Real Name'),
            'nickname'     => Yii::t('base', 'Nickname'),
            'slug'         => Yii::t('base', 'Slug'),
            'createdTime'  => Yii::t('base', 'Created Time'),
            'updatedTime'  => Yii::t('base', 'Updated Time'),
        ];
    }

    // 借鉴 HTTP 状态码
    // 2xx 200 OK 201 Created(Inactive)
    // 3xx
    // 4xx 403 Forbidden 404 Not Found(Deleted) 423 Locked
    // 5xx
    const STATUS_OK        = 200;
    const STATUS_INACTIVE  = 201;
    const STATUS_FORBIDDEN = 403;
    const STATUS_DELETED   = 404;
    const STATUS_LOCKED    = 423;

    /**
     * @param null $key
     * @param null $default
     * @return array|mixed
     */
    public static function presetStatuses($key = null, $default = null)
    {
        $data = [
            self::STATUS_OK        => ['name' => Yii::t('base', 'OK')],
            self::STATUS_INACTIVE  => ['name' => Yii::t('base', 'Inactive')],
            self::STATUS_FORBIDDEN => ['name' => Yii::t('base', 'Forbidden')],
            self::STATUS_DELETED   => ['name' => Yii::t('base', 'Deleted')],
            self::STATUS_LOCKED    => ['name' => Yii::t('base', 'Locked')],
        ];
        return $key === null ? $data : ArrayHelper::getValue($data, $key, $default);
    }

    const ROLE_MEMBER = 0;
    const ROLE_ADMIN  = 9;

    /**
     * @param null $key
     * @param null $default
     * @return array|mixed
     */
    public static function presetRoles($key = null, $default = null)
    {
        $data = [
            self::ROLE_MEMBER => ['name' => Yii::t('base', 'Member')],
            self::ROLE_ADMIN  => ['name' => Yii::t('base', 'Admin')],
        ];
        return $key === null ? $data : ArrayHelper::getValue($data, $key, $default);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['status', 'required'],
            ['status', 'integer', 'min' => 100, 'max' => 999],
            ['status', 'in', 'range' => array_keys(static::presetStatuses())],

            ['role', 'required'],
            ['role', 'integer', 'min' => 0, 'max' => 255],
            ['role', 'in', 'range' => array_keys(static::presetRoles())],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsermbr()
    {
        return $this->hasOne(Usermbr::class, ['mbrUid' => 'uid']);
    }

    /**
     * @inheritdoc
     * 
     * @param int|string $uid
     * @return self|null
     */
    public static function findIdentity($uid)
    {
         return is_numeric($uid) ? static::findOne(['uid' => $uid]) : null;
    }

    /**
     * 通过加密的 token 查找用户
     *
     * @param string $accessToken 解密后的格式为 time()|uid|authKey[43]
     * @param null $type
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function findIdentityByAccessToken($accessToken, $type = null)
    {
        try {
            $accessToken = Yii::$app->security->decryptByKey(
                base64_decode($accessToken),
                Yii::$app->request->cookieValidationKey);
            list($time, $uid, $authKey) = explode('|', $accessToken, 3);
        } catch (\Exception $e) {
            return null;
        }

        // 判断是否过期（系统用户不限制）
        if (time() - $time > Yii::$app->params['userRememberMe']) {
            return null;
        }
        // uid authKey 格式是否正确
        if (is_numeric($uid) && strlen($authKey) == 43) {
            return static::find()
                ->where(['uid' => $uid, 'authKey' => $authKey])
                ->andWhere(['in', 'status', [self::STATUS_OK, self::STATUS_INACTIVE]])
                ->one();
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->uid;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Generates "remember me" authentication key(43)
     */
    public function setAuthKey()
    {
        $this->authKey = time() . '-' . Yii::$app->security->generateRandomString();
    }

    /**
     * Decrypt password
     *
     * @return bool|string
     */
    public function getPassword()
    {
        return Yii::$app->security->decryptByKey(
            base64_decode($this->passwordHash), $this->getAuthKey()
        );
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function setPassword($password)
    {
        if (empty($this->authKey)) {
            throw new \yii\base\InvalidConfigException('AuthKey must be set.');
        }
        $this->passwordHash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * 使用 time()|id|authKey 加密生成 accessToken
     *
     * @return string
     */
    public function generateAccessToken()
    {
        return base64_encode(Yii::$app->security->encryptByKey(
            time() . '|' . $this->uid . '|' . $this->authKey,
            Yii::$app->request->cookieValidationKey
        ));
    }

    /**
     * @inheritdoc
     */
    public function saveInput()
    {
        parent::saveInput();

        if ($this->username == '') {
            $this->username = null;
        }
        if ($this->email == '') {
            $this->email = null;
        }
        if ($this->slug == '') {
            $this->slug = null;
        }
    }

    /**
     * @param $row
     */
    public static function queryOutputBy(&$row)
    {
        foreach ($row as $field => $value) {
            switch ($field) {
                case 'status':
                    $row['statusName'] = User::presetStatuses("{$value}.name");
                    break;
                case 'role':
                    $row['roleName'] = User::presetRoles("{$value}.name");
                    break;
            }
        }
    }
}
