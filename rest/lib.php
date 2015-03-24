<?php

function createDateForJavaScript($timestamp) {
    $formatPrefix = "Y,";
    // Javascript starts counting with 0
    $month = ((int) date('m', $timestamp)) - 1;
    $formatSuffix = ",d,H,i,s";
    return "Date(" . date($formatPrefix, $timestamp) . $month . date($formatSuffix, $timestamp) . ")";
}

?>