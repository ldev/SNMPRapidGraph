<?php
    require_once('../db_connect.php');
    header("Content-type: text/json");
    $divider = 1000000;
    $json = array();
    if($res = pg_query($db, "SELECT bps, time FROM data WHERE direction = 'inbound' ORDER BY time DESC LIMIT 1;")){
        if(pg_num_rows($res) === 1){
            $data = pg_fetch_object($res);
            $json['inbound'] = array($data->time, (float) round($data->bps/$divider, 2));
        }
    }
    
    if($res = pg_query($db, "SELECT bps, time FROM data WHERE direction = 'outbound' ORDER BY time DESC LIMIT 1;")){
        if(pg_num_rows($res) === 1){
            $data = pg_fetch_object($res);
            $json['outbound'] = array($data->time, (float) round($data->bps/$divider, 2));
        }
    }
    echo json_encode($json);
