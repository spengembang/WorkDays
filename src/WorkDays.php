<?php 

/*
 * (c) Sang Pengembang <sinaunengweb@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

// If you don't to add a custom vendor folder, then use the simple class
// namespace WorkDays;
namespace spengembang\WorkDays;

class WorkDays
{
    function getWorkingDays($startDate, $endDate, $wday, $proType = 1, $holidays=false){
        $strEndDate = $endDate;
        $endDate = strtotime($endDate);
        $strStartDate = $startDate;
        $startDate = strtotime($startDate);
        $days = (($endDate - $startDate) / 86400) + 1;
        if($proType == 1){
            $multiplier = ($wday == 5) ? 2 : 1;
            $workingDays = $days - ($multiplier * (floor($days/7)));
            return $workingDays;
        }elseif($proType == 2){    
            $no_full_weeks = floor($days / 7);
            $no_remaining_days = fmod($days, 7);
            $the_first_day_of_week = date("N", $startDate);
            $the_last_day_of_week = date("N", $endDate);
            if ($the_first_day_of_week <= $the_last_day_of_week) {
                if ($the_first_day_of_week <= 6 && 6 <= $the_last_day_of_week)
                    $no_remaining_days--;
                if ($the_first_day_of_week <= 7 && 7 <= $the_last_day_of_week)
                    $no_remaining_days--;
                }else{
                    if ($the_first_day_of_week == 7) {
                        $no_remaining_days--;
                        if($wday == 5){
                            if ($the_last_day_of_week == 6) {
                                $no_remaining_days--;
                            }
                        }
                    }
                    else{
                        $no_remaining_days -= 2;
                    }
                }
            $workingDays = $no_full_weeks * $wday;
            if ($no_remaining_days > 0 ){
                $workingDays += $no_remaining_days;
            }
            $workingDays = $workingDays+1;
            if($holidays){
                foreach($holidays as $holiday){
                    $time_stamp=strtotime($holiday);
                    if($wday == 5){
                        if ($startDate <= $time_stamp && $time_stamp <= $endDate && date("N",$time_stamp) != 6 && date("N",$time_stamp) != 7) {
                          $workingDays--;
                        }
                    }else{
                        if ($startDate <= $time_stamp && $time_stamp <= $endDate && date("N",$time_stamp) != 7) {
                            $workingDays--;
                        }
                    }
                }
            }
            return $workingDays;
        }elseif($proType == 3){
            return $days;
        }else{
            return 'ERR_UNKNOWN';
        }
    }
}