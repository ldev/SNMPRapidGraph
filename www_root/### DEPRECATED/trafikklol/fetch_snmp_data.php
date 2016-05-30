<?php
    header("Content-type: text/json");
    chdir(__DIR__); # Sets the CWD to the script path
    require('ramdisk_testing.class.php');

    $return = array();

    $ramdisk = new ramdisk();
    $ramdisk->debug(false);
    
    $identifier = 'inbound';
    $x = snmp2_get('80.212.218.195', 'lolol', '1.3.6.1.2.1.31.1.1.1.6.3'); # HC in - 64 bits counter
    
    $value = substr($x, 11);
    $store_value = $value;
    if($age = $ramdisk->identify($identifier)->age()){
        $js_timestamp = $ramdisk->identify($identifier)->timestamp(true);
        $prev_value = $ramdisk->identify($identifier)->get();
        if($prev_value > $value){
            $value = round(($value+18446744073709551615-$prev_value)/$age)*8;
        }elseif($prev_value == $value && $age < 5){
            $value = 0;
        }else{
            $value = round(($value-$prev_value)/$age)*8;
        }
        $return['inbound'] = array($js_timestamp, round($value/1000000,2));
    }
    $ramdisk->identify($identifier)->set($store_value);
    
    /* --------------- */
    
    // Outbound
    $identifier = 'outbound';
    $x = snmp2_get('80.212.218.195', 'lolol', '1.3.6.1.2.1.31.1.1.1.10.3'); # HC in - 64 bits counter

    $value = substr($x, 11);
    $store_value = $value;
    if($age = $ramdisk->identify($identifier)->age()){
        $js_timestamp = $ramdisk->identify($identifier)->timestamp(true);
        $prev_value = $ramdisk->identify($identifier)->get();
        if($prev_value > $value){
            $value = round(($value+18446744073709551615-$prev_value)/$age)*8;
        }elseif($prev_value == $value && $age < 5){
            $value = 0;
        }else{
            $value = round(($value-$prev_value)/$age)*8;
        }   
        $return['outbound'] = array($js_timestamp, round($value/1000000,2));
    }
    $ramdisk->identify($identifier)->set($store_value);
    echo json_encode($return);
