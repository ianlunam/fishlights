&value=<?php

$WANTS = 'otmp';
if (array_key_exists('data', $_GET)) {
  $WANTS = strtolower($_GET['data']);
}

$json_a = json_decode(file_get_contents("/etc/fishtimer/current.data"), true);

$CHANNEL_WHITE = $json_a[0];
$CHANNEL_BLUE = $json_a[1];
$CHANNEL_STD = $json_a[2];
$CHANNEL_LATER = $json_a[3];

switch ($WANTS) {
  case "white":
    echo $CHANNEL_WHITE;
    break;
  case "blue":
    echo $CHANNEL_BLUE;
    break;
  case "std":
    echo $CHANNEL_STD;
    break;
  case "later":
    echo $CHANNEL_LATER;
    break;
  default:
    echo $CHANNEL_WHITE;
}

?>
