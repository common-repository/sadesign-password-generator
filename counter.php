<?php
$count = $_POST["count"];
$countOld = 0;
if(get_option('passcounter')) {
  $countOld = get_option('passcounter');
}
$countNew = $count + $countOld;
update_option( 'passcounter', $countNew );
