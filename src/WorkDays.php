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

use DateTime;

class WorkDays
{
    
    /**
     * getWorkingDays is to get how many day of working day
     *
     * @param  date $startDate
     * @param  date $endDate
     * @param  integer $wday
     * @param  enum $proType
     * @param  integer $holidays
     *
     * @return void
     */
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

    /**
     * getProrate
     *
     * @param  mixed $startdate
     * @param  mixed $enddate
     * @param  mixed $periodestart
     * @param  mixed $periodeend
     * @param  mixed $wday
     * @param  mixed $value
     * @param  mixed $simid
     * @param  mixed $holidays
     * @param  mixed $debug
     * @param  mixed $new_hire
     *
     * @return void
     */
    function getProrate($startdate, $enddate, $periodestart, $periodeend, $wday, $value, $simid = null, $holidays=false, $debug=false, $new_hire=false){
        $startdate = formatTanggal($startdate);
        $enddate = formatTanggal($enddate);
        if($protype == 3){
            $dmonth = getActiveDays($periodestart, $periodeend);
        }else{
            if ($wday == 5) {
                $dmonth = 21;
            } else {
                $dmonth = 25;
            }
        }
        $temp = $value;

        $prorateDepanBelakang = $prorateDepan = $prorateBelakang = 0;
        if($startdate > $periodestart){
            if($enddate < $periodeend){
                $holidays = holidayprorate($simid, $startdate, $enddate);
                $prorateDepanBelakang = getWorkingDays($startdate, $enddate, $wday, $protype, $holidays);
            }else{
                $holidays = holidayprorate($simid, $startdate, $periodeend);
                $prorateDepan = getWorkingDays($startdate, $periodeend, $wday, $protype, $holidays);
            }
        }
        if($enddate < $periodeend){
            $holidays = holidayprorate($simid, $periodestart, $enddate);
            $prorateBelakang = getWorkingDays($periodestart, $enddate, $wday, $protype, $holidays);
        }

        if($prorateDepan){//prorate front
            if(!$new_hire){
                $holidays = holidayprorate($simid, $periodestart, formatTanggal($startdate, '-1 day'));
                $prorateDepan = getWorkingDays($periodestart, formatTanggal($startdate, '-1 day'), $wday, $protype, $holidays);
                $activeday = min($dmonth, ($dmonth - $prorateDepan));
                $nilai = ($activeday / $dmonth) * $value;
            }else{
                echo 'THIS IS NEW HIRE </br>';
                $holidays = holidayprorate($simid, $startdate, $periodeend);
                $prorateDepan = getWorkingDays($startdate, $periodeend, $wday, $protype, $holidays);//dev_dd($prorateDepan);
                $activeday = min($dmonth, $prorateDepan);
                $nilai = ($activeday / $dmonth) * $value;
            }
        }elseif($prorateDepanBelakang){//prorate front and back
            $activeday = min($dmonth, $prorateDepanBelakang);
            $nilai = ($activeday/$dmonth) * $value;
        }elseif($prorateBelakang){//prorate back
            $activeday = min($dmonth, $prorateBelakang);
            $nilai = ($activeday/$dmonth) * $value;
        }else{
            $nilai = $value;
        }
    // echo '--------------------------------------------------------------- </br>';
        if(($debug == TRUE) or (strtoupper($debug) == 'DEBUG')){
            $nilai = number_format(round($nilai,0,PHP_ROUND_HALF_UP));
            $result = 'Interval date: <font color="green">'.$startdate.'</font> s/d <font color="green">'.$enddate.
            '</font><br />Periode date: <font color="blue">'.$periodestart.'</font> s/d <font color="blue">'.$periodeend.
            '</font><br />Hari kerja dalam 1 bulan: <font color="blue">'.$dmonth.'</font> <i><font size=2>('.$wday.' hari kerja)</font></i>'.
            (!empty($activeday) ? '<br />Hari kerja aktif: <font color="green">'.$activeday.'</font>': '<br />No Prorate').
            '<br />Value yg di prorate: <font color="blue">'.number_format($temp).
            '</font><br />Hasil Prorate: <font color="green">'.$nilai.'</font>';
        }else{
            $result = round($nilai,0,PHP_ROUND_HALF_UP);
        }
        if($result > 0)
            return $result;
    }

    /**
     * getActiveDays
     *
     * @param  mixed $startdate
     * @param  mixed $enddate
     * @param  mixed $debug
     *
     * @return void
     */
    function getActiveDays($startdate, $enddate, $debug = false){
        $start_date = new DateTime($startdate);
        $end_date = new DateTime($enddate);
        $interval = $start_date->diff($end_date);
        if($debug == true){
            $hasil = "Start Date: ".$startdate. "<br>End Date: " .$enddate ."<br>Beda ".(($interval->days) + 1). " hari";
        }else{
            $hasil = ($interval->days)+1;
        }
        return $hasil;
    }
}