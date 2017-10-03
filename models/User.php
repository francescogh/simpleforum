<?php

namespace app\models;

class User extends \yii\db\ActiveRecord  implements \yii\web\IdentityInterface
{
    /*
	 * @property integer $id
	 * @property string $username
	 * @property string $password
	 * @property string $authKey
    */	

    public static function tableName()
    {
        return 'user';
    }
    
	/**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
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
        return $this->authKey === $authKey;
    }
    
	/**
     * Finds an identity by the given token.
     *
     * @param string $token the token to be looked for
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
    	// ...
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return $this->password === $password;
    }
}
