<?php

// $skipdays: array (Monday-Sunday) eg. array("Saturday","Sunday")
// $skipdates: array (YYYY-mm-dd) eg. array("2012-05-02","2015-08-01");
// $date is (YYYY-mm-dd)
function add_working_days($date, $days, $skipdays = array("Saturday", "Sunday"), $skipdates = array()) {
    $timestamp=strtotime($date);
    $i = 1;

    while ($days >= $i) {
        $timestamp = strtotime("+1 day", $timestamp);
        if (in_array(date("l", $timestamp), $skipdays)) {
            $days++;
        }else if (in_array(date("Y-m-d",$timestamp), $skipdates)){
            $days++;
        }
        $i++;
    }

    return date("Y-m-d",$timestamp);
}