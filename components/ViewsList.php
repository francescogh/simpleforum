<?php

namespace app\components;

use yii\helpers\Json;

class ViewsList
{
	/*
	 * Time after which another view on the same resource-id should be counted, in seconds.
	 */
	const EXPIRING_TIME = 86400;
	
	/*
	 * Array that store the last view datetimes for each resourse id
	 * only if they occurred for less then EXPIRING_TIME seconds
	 */
	private $lastViewsDateTimes;
	
	private function __construct(){
		$this->lastViewsDateTimes = array();
	}
	
	/*
	 * Create a ViewsList with the given json list of old views
	 * obmitting the views that are too old
	 * 
	 * @param $jsonOldViewsDateTimes
	 * @return ViewsList
	 */
	public static function create($jsonOldViewsDateTimes)
	{
		$viewsList = new ViewsList();
		
		$oldViewsDateTimes = Json::decode($jsonOldViewsDateTimes);
		
		foreach($oldViewsDateTimes as $id => $datetime)
		{			
			// the old views are still new enough ?
			if(time() - $datetime <= self::EXPIRING_TIME)
			{
				// reuse the latter datetime;
				$viewsList->lastViewsDateTimes[$id] = $datetime;
			}			
		}		
		
		return $viewsList;
	}
	
	/*
	 *  @param $newId the id of the last resource visited
	 *  return boolean It will be true if the view for the resource is new enough 
	 */
	public function add($newId)
	{		
		if(!isset($this->lastViewsDateTimes[$newId])
		|| time() - $this->lastViewsDateTimes[$newId] > self::EXPIRING_TIME)
		{
			// use the current datetime;
			$this->lastViewsDateTimes[$newId] = time(); 
			return true;
		}
		
		return false;
	}

	/*
	 * return the json serialized list of views
	 */
	public function getSerialized()
	{
		return Json::encode($this->lastViewsDateTimes);
	}
}