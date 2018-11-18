<html>
<head>
<link rel="stylesheet" href="/style.css" type="text/css" charset="utf-8" /> 
<title>Freddie The Fish and Friends</title>
<script type="text/javascript" src="js/fusioncharts.js"></script>
<script type="text/javascript" src="js/themes/fusioncharts.theme.ocean.js"></script>
<?php

include("includes/fusioncharts.php");

$json_a = json_decode(file_get_contents("/etc/fishtimer/current.data"), true);

$CHANNEL_WHITE = $json_a[0];
$CHANNEL_BLUE = $json_a[1];
$CHANNEL_STD = $json_a[2];
$CHANNEL_LATER = $json_a[3];

$chartWhite = new FusionCharts("AngularGauge", "ex1", "200", "150", "chart-white", "json", '{
    "chart": {
        "dataStreamUrl": "update.php?data=white",
        "refreshInterval": "1",
        "caption": "White Lights",
        "lowerlimit": "0",
        "upperlimit": "255",
        "showValue": "0",
        "showTickMarks": "0",
        "showTickValues": "0",
        "valueBelowPivot": "0",
        "gaugeFillMix": "{dark-30},{light-60},{dark-10}",
        "gaugeFillRatio": "15",
        "gaugeInnerRadius": "40%",
        "theme": "fint"
    },
    "colorrange": {
        "color": [
            { "minvalue": "0", "maxvalue": "255", "code": "ffffff" }
        ]
    },
    "dials": {
        "dial": [
            {
                "value": "' . $CHANNEL_WHITE . '",
                "rearextension": "8",
                "radius": "85",
                "bgcolor": "333333",
                "bordercolor": "333333"
            }
        ]
    }
}');
$chartWhite->render();

$chartBlue = new FusionCharts("AngularGauge", "ex2", "200", "150", "chart-blue", "json", '{
    "chart": {
        "dataStreamUrl": "update.php?data=blue",
        "refreshInterval": "1",
        "caption": "Blue Lights",
        "lowerlimit": "0",
        "upperlimit": "255",
        "showTickMarks": "0",
        "showTickValues": "0",
        "showValue": "0",
        "valueBelowPivot": "0",
        "gaugeFillMix": "{dark-30},{light-60},{dark-10}",
        "gaugeFillRatio": "15",
        "gaugeInnerRadius": "40%",
        "theme": "fint"
    },
    "colorrange": {
        "color": [
            { "minvalue": "0", "maxvalue": "255", "code": "0000ff" }
        ]
    },
    "dials": {
        "dial": [
            {
                "value": "' . $CHANNEL_BLUE . '",
                "rearextension": "8",
                "radius": "85",
                "bgcolor": "333333",
                "bordercolor": "333333"
            }
        ]
    }
}');
$chartBlue->render();

$chartStd = new FusionCharts("AngularGauge", "ex3", "200", "150", "chart-std", "json", '{
    "chart": {
        "dataStreamUrl": "update.php?data=std",
        "refreshInterval": "1",
        "caption": "Standard Lights",
        "lowerlimit": "0",
        "upperlimit": "255",
        "showTickMarks": "0",
        "showTickValues": "0",
        "showValue": "0",
        "valueBelowPivot": "0",
        "gaugeFillMix": "{dark-30},{light-60},{dark-10}",
        "gaugeFillRatio": "15",
        "gaugeInnerRadius": "40%",
        "theme": "fint"
    },
    "colorrange": {
        "color": [
            { "minvalue": "0", "maxvalue": "255", "code": "aaaaff" }
        ]
    },
    "dials": {
        "dial": [
            {
                "value": "' . $CHANNEL_STD . '",
                "rearextension": "8",
                "radius": "85",
                "bgcolor": "333333",
                "bordercolor": "333333"
            }
        ]
    }
}');
$chartStd->render();

$chartLater = new FusionCharts("AngularGauge", "ex4", "200", "150", "chart-later", "json", '{
    "chart": {
        "dataStreamUrl": "update.php?data=later",
        "refreshInterval": "5",
        "caption": "Empty Channel",
        "lowerlimit": "0",
        "upperlimit": "255",
        "showTickMarks": "0",
        "showTickValues": "0",
        "showValue": "0",
        "valueBelowPivot": "0",
        "gaugeFillMix": "{dark-30},{light-60},{dark-10}",
        "gaugeFillRatio": "15",
        "gaugeInnerRadius": "40%",
        "theme": "fint"
    },
    "colorrange": {
        "color": [
            { "minvalue": "0", "maxvalue": "255", "code": "000000" }
        ]
    },
    "dials": {
        "dial": [
            {
                "value": "' . $CHANNEL_LATER . '",
                "rearextension": "8",
                "radius": "85",
                "bgcolor": "333333",
                "bordercolor": "333333"
            }
        ]
    }
}');
$chartLater->render();

?>

<script>
var imageCount = 1;

function setImageTimer() {
  // Set timer to happen at the end of the next minute
  var d = new Date();
  setTimeout(function(){updateImages()}, ((60 - d.getSeconds()) * 1000));
}

function updateImages() {
  for (x = 1; x <= imageCount; x++) {
    updateImage(x);
  }
  // Set timer to happen at the end of the next minute
  var d = new Date();
  setTimeout(function(){updateImages()}, ((60 - d.getSeconds()) * 1000));
}

function updateImage(imageNum) {
  // Update image, forced by addition of current date to URI
  var imageName = "webcam" + imageNum;
  var image = document.getElementById(imageName);
  if ( image.complete ) {
    image.src = "/motion/lastsnap.jpg?time=" + new Date();
  }
}

function clicketh(what) {
  fetch("action.php?what=" + what)
}

</script>
<style media="screen" type="text/css">
.one {
    width: 33%;
    height: 200px;
    float: left;
}
.two {
    width: 33%;
    height: 200px;
    float: left;
}
.three {
    width: 33%;
    height: 200px;
    float: left;
}
    .slider-wrapper {
        display: inline-block;
        width: 20px;
        height: 150px;
        padding: 0;
    }
    .slider-wrapper input {
        width: 150px;
        height: 20px;
        margin: 0;
        transform-origin: 75px 75px;
        transform: rotate(-90deg);
    }


    .button1 {
       background-color: #0000ff;
       border-radius: 10px;
       border: 2px solid #0000ff;
       color: white;
       padding: 15px 32px;
       text-align: center;
       text-decoration: none;
       display: inline-block;
       font-size: 16px;
    }

    .button2 {
       background-color: #aaaaff;
       border: 2px solid #000000;
       border-radius: 10px;
       color: black;
       padding: 15px 26px;
       text-align: center;
       text-decoration: none;
       display: inline-block;
       font-size: 16px;
    }

    .button3 {
       background-color: #eeeeff;
       border: 2px solid #000000;
       border-radius: 10px;
       color: black;
       padding: 15px 39px;
       text-align: center;
       text-decoration: none;
       display: inline-block;
       font-size: 16px;
    }

    .button4 {
       background-color: #000000;
       border: 2px solid #000000;
       border-radius: 10px;
       color: white;
       padding: 15px 36px;
       text-align: center;
       display: inline-block;
       font-size: 16px;
    }

    .button5 {
       background-color: #ff0000;
       border: 2px solid #000000;
       border-radius: 10px;
       color: black;
       padding: 15px 28px;
       text-align: center;
       display: inline-block;
       font-size: 16px;
    }
</style>
</head>
<body>
</head>
<body onload="setImageTimer();">
<center>
<table width="600" border="0">
  <tr><td width="400">
    <table width="400" border="2">
      <tr><th colspan="2">Light Status</th></tr>
      <tr>
        <td width="50%"><div id="chart-white"></div></td>
        <td width="50%"><div id="chart-blue"></div></td>
      </tr>
      <tr>
        <td width="50%"><div id="chart-std"></div></td>
        <td width="50%"><div id="chart-later"></div></td>
      </tr>
      <!-- tr><td colspan="2">
        <a href="/motion/big.jpg"><img id="webcam1" width="176" height="144" src="/motion/lastsnap.jpg"/></a>
        <div class="slider-wrapper">
          <input type="range" min="0" max="255" value="127" step="1" id="blue" name="blue">
        </div>
        <label for="blue">Blue</label>
        (<span id="demo"></span>)
      </td></tr -->
    </table>
  </td>
  <td>
    <table>
      <tr><th>Give me 10<br>minutes of</th></tr>
      <tr><td><input type="button" class="button1" onclick="clicketh('blue')" value="Blue"></td></tr>
      <tr><td><input type="button" class="button2" onclick="clicketh('bluish')" value="Bluish"></td></tr>
      <tr><td><input type="button" class="button3" onclick="clicketh('all')" value="All"></td></tr>
      <tr><td><input type="button" class="button4" onclick="clicketh('off')" value="Off"></td></tr>
      <tr><td><input type="button" class="button5" onclick="clicketh('reset')" value="Reset"></td></tr>
    </table>
  </td></tr>
</table>
</center>
<script>
var slider = document.getElementById("blue");
var output = document.getElementById("demo");
output.innerHTML = slider.value; // Display the default slider value

// Update the current slider value (each time you drag the slider handle)
slider.oninput = function() {
    output.innerHTML = this.value;
}
</script>
</body>
</html>
