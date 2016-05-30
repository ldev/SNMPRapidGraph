<?php
    if(!$db = pg_connect('host=localhost dbname=snmprapidgraph user=<user> password=<password>')){
        die('DB connection error');
    }
