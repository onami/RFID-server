<?php
$dbname = "rfid";
$basedir = "/rfid";

ORM::configure('mysql:host=localhost;dbname='.$dbname);
ORM::configure('username', 'root');
ORM::configure('password', '');
ORM::get_db()->exec('set names utf8');
?>