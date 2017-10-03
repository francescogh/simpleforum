<?php
 
namespace app\components;

use yii\validators\Validator;

/*
 * This Validator unallow content that match any pattern in $patterns 
 */
class BadPatternsValidator extends Validator
{	
	static $patterns = [
		"/\?{2,}/",
		"/@{2,}/",
		"/#{2,}/"
	];	
	
    public function validateAttribute($model, $attribute)
    {
    	foreach(static::$patterns as $pattern)
    	{
    		if(preg_match($pattern, $model->$attribute))
    		{
    			$this->addError($model, $attribute, $attribute . ' contains not allowed patterns');
    			break;
    		}
    	}
    }
}