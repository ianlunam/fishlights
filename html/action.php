<?php

$BASE_DIR = '/etc/fishtimer/';
$WEEKDAY_SCHEDULE = $BASE_DIR . 'schedule.conf.weekday';
$WEEKEND_SCHEDULE = $BASE_DIR . 'schedule.conf.weekend';
$TODAY_SCHEDULE = $BASE_DIR . 'schedule.conf';

$SHEDULES = array("Mon" => $WEEKDAY_SCHEDULE,
                  "Tue" => $WEEKDAY_SCHEDULE,
                  "Wed" => $WEEKDAY_SCHEDULE,
                  "Thu" => $WEEKDAY_SCHEDULE,
                  "Fri" => $WEEKDAY_SCHEDULE,
                  "Sat" => $WEEKEND_SCHEDULE,
                  "Sun" => $WEEKEND_SCHEDULE);

function setChannel($channel, $new_value) {
  $now = date("H:i");
  $in10 = date("H:i", strtotime("+10 minutes"));
  $in11 = date("H:i", strtotime("+11 minutes"));
  $keys = array_keys($channel);
  for($i = 0; $i < count($channel); ++$i) {
    $thisOne = $channel[$keys[$i]];
    if ($keys[$i] > $now) {
      # This is where we need to work
      if ($keys[$i+1] > $in11) {
        # Not going to clash with anything
        $channel = array_slice($channel, 0, $i, true) +
                   array($now => $new_value) +
                   array($in10 => $new_value) +
                   array($in11 => $channel[$keys[$i]]) +
                   array_slice($channel, $i, NULL, true);
      } else {
        # Clash with next happening, need to shuffle
        $channel = array_slice($channel, 0, $i, true) +
                   array($now => $new_value) +
                   array_slice($channel, $i, NULL, true);
      }
      return $channel;
    }
  }
  return $channel;
}

function saveSchedule($filename, $schedule) {
  $fp = fopen($filename, 'w');
  fwrite($fp, json_encode($schedule, JSON_PRETTY_PRINT));
  fclose($fp);
}


$WANTS = 'none';
if (array_key_exists('what', $_GET)) {
  $WANTS = strtolower($_GET['what']);
}

$current_schedule = json_decode(file_get_contents($TODAY_SCHEDULE), true);
$CHANNEL_WHITE = $current_schedule['schedules'][0]['schedule'];
$CHANNEL_BLUE = $current_schedule['schedules'][1]['schedule'];
$CHANNEL_STD = $current_schedule['schedules'][2]['schedule'];
$CHANNEL_LATER = $current_schedule['schedules'][3]['schedule'];

switch ($WANTS) {
  case "blue":
    $current_schedule['schedules'][0]['schedule'] = setChannel($CHANNEL_WHITE, 0);
    $current_schedule['schedules'][1]['schedule'] = setChannel($CHANNEL_BLUE, 255);
    $current_schedule['schedules'][2]['schedule'] = setChannel($CHANNEL_STD, 0);
    saveSchedule($TODAY_SCHEDULE, $current_schedule);
    break;
  case "bluish":
    $current_schedule['schedules'][0]['schedule'] = setChannel($CHANNEL_WHITE, 100);
    $current_schedule['schedules'][1]['schedule'] = setChannel($CHANNEL_BLUE, 255);
    $current_schedule['schedules'][2]['schedule'] = setChannel($CHANNEL_STD, 0);
    saveSchedule($TODAY_SCHEDULE, $current_schedule);
    break;
  case "all":
    $current_schedule['schedules'][0]['schedule'] = setChannel($CHANNEL_WHITE, 255);
    $current_schedule['schedules'][1]['schedule'] = setChannel($CHANNEL_BLUE, 255);
    $current_schedule['schedules'][2]['schedule'] = setChannel($CHANNEL_STD, 255);
    saveSchedule($TODAY_SCHEDULE, $current_schedule);
    break;
  case "off":
    $current_schedule['schedules'][0]['schedule'] = setChannel($CHANNEL_WHITE, 0);
    $current_schedule['schedules'][1]['schedule'] = setChannel($CHANNEL_BLUE, 0);
    $current_schedule['schedules'][2]['schedule'] = setChannel($CHANNEL_STD, 0);
    saveSchedule($TODAY_SCHEDULE, $current_schedule);
    break;
  case "reset":
    copy($SHEDULES[date('D')], $TODAY_SCHEDULE);
    break;
}

?>
