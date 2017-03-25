<?php
require_once('wordpress-availability-class.php');

$websites=file("domains");

$live = new Availability;

$live->parse($websites);

?>
