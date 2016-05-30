<?php
    require_once('ramdisk_testing.class.php');
    $ramdisk = new ramdisk();
    var_dump($ramdisk->identify('asdf')->age());
    var_dump($ramdisk->identify('asdf')->timestamp(true));
    $value = $ramdisk->identify('asdf')->get();
    $ramdisk->identify('asdf')->set(rand(0, 100));
    var_dump($value);
    # $age = str_replace('.', '', $age);
?>
