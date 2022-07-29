<?php

class ModelModuleTopBannerAd extends Model {

	public function getDynamicOrderedTimeSlots()
	{
		$slots = $this->config->get('top_banner_dynamic_timing_slots');

		//sort dates by start dates
		usort($slots, function($a, $b) {
		  $startDate1 = new DateTime($a['start_date']);
		  $startDate2 = new DateTime($b['start_date']);
	      return $startDate1 <=> $startDate2;
		});

		$filtered_slots = $this->filterSlots($slots);
		//array_values for fixing keys
		return array_values($this->convertToRemainingMiliseconds($filtered_slots));
	}

	/**
	 * Remove past/old slots, remove duplicated & merge the inclusive ones, 
	 * 
	 */ 
	private function filterSlots($slots)
	{
		$refinedSlots = [];
		$previousSlot = [];

		foreach($slots as $index => $slot){
			
			//1- Assigning currentStartDate
			// check if currentStartDate is null assign current slot start_date to it.			
			// $currentStartDate = $currentStartDate == null ? $slot['start_date'] : $currentStartDate;
			if( empty($previousSlot) ){
				$previousSlot = $slot;
				$previousSlot['index'] = $index;
				$refinedSlots[] = $slot;
				continue;
			}
			//2- Compare
			if($slot['start_date'] >= $previousSlot['start_date']){
				if( $slot['start_date'] > $previousSlot['end_date'] ){
					$previousSlot = $slot;
					$previousSlot['index'] = $index;
					$refinedSlots[] = $slot;
				}
				else /*if($slot['start_date'] <= $previousSlot['end_date'])*/{
					if($slot['end_date'] > $previousSlot['end_date']){
						$refinedSlots[$previousSlot['index']]['end_date'] = $slot['end_date'];
					}
				}
			}
		}
		// $now_date = (new DateTime('NOW'))->format('Y-m-d H:i');
		$now_date = $this->config->get('config_timezone') ? new DateTime("now", new DateTimeZone($this->config->get('config_timezone'))) : new DateTime("now");
		$now_date = (int)$now_date->format("U");

		$refinedSlots = array_filter($refinedSlots, function($slot) use($now_date){
        	$end_date   = new DateTime($slot['end_date']);
			return (int)$end_date->format("U") - $now_date > 0;
			// return $slot['end_date'] >= $now_date;
		});
		return $refinedSlots;
	}

	private function convertToRemainingMiliseconds($slots)
	{
		$now = $this->config->get('config_timezone') ? new DateTime("now", new DateTimeZone($this->config->get('config_timezone'))) : new DateTime("now");
		$now = (int)$now->format("U");

        return array_map(function($slot) use ($now){
        	$start_date = new DateTime($slot['start_date']);
        	$end_date   = new DateTime($slot['end_date']);

        	$start_diff = (int)$start_date->format("U") - $now;
        	$end_diff = (int)$end_date->format("U") - (int)$start_date->format("U");

        	return [
        		'to_start_date' => ($start_diff > 0) ? $start_diff : 0,
        		'to_end_date'   => $end_diff
        	];
        }, $slots);
	}
}
