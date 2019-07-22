<?php
require_once '../vendor/autoload.php';

use WhizSid\OutOfWay\OutOfWay;
use WhizSid\OutOfWay\VehicleCoordinate;

$passed = json_decode(file_get_contents('passed.json'), true);
$vehicle = json_decode(file_get_contents('vehicle.json'), true);

$outOfWay = new OutOfWay;

$outOfWay->setFilteration(false);

$formatedVehicle = [];
$formatedPassed = [];

foreach ($vehicle as $vehicleCoordinate) {
    $formatedVehicle[] = new VehicleCoordinate($vehicleCoordinate['lat'], $vehicleCoordinate['lng']);
}

foreach ($passed as $passedCoordinate) {
    $formatedPassed[] = new VehicleCoordinate($passedCoordinate['lat'], $passedCoordinate['lng']);
}
// \xdebug_break();
$outOfWay->setActualCoordinates($formatedPassed);
$outOfWay->setVehicleCoordinates($formatedVehicle);

$contents = json_encode($outOfWay->getMatchedCoordinates());

file_put_contents('returned.json', $contents);

?>
<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <title>Simple Polylines</title>
    <script
        src="https://code.jquery.com/jquery-3.4.1.min.js"
        integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
        crossorigin="anonymous"></script>
    <style>
      /* Always set the map height explicitly to define the size of the div
       * element that contains the map. */
      #map {
        height: 100%;
      }
      /* Optional: Makes the sample page fill the window. */
      html, body {
        height: 100%;
        margin: 0;
        padding: 0;
      }

      .color {
        width: 24px;
        height: 24px;
        display: inline-block;
      }

      .red {
          background:red
      }

      .green {
          background:green;
      }

      .blue {
          background: blue;
      }

      #description {
          position:fixed;
          bottom:0;
          left:0;
          background:white;
      }
    </style>
  </head>
  <body>
    <div id="map"></div>
    <div id="description">
        <span class="color red"></span> Actual Road  <span class="color green"></span> Vehicle Path <span class="color blue" ></span> Calculated Path
    </div>
    <script>

      // This example creates a 2-pixel-wide red polyline showing the path of
      // the first trans-Pacific flight between Oakland, CA, and Brisbane,
      // Australia which was made by Charles Kingsford Smith.

      function initMap() {
        var map = new google.maps.Map(document.getElementById('map'), {
          zoom: 12,
          center: {
                "lat":6.92621,
                "lng":80.21887
            },
          mapTypeId: 'terrain'
        });


        $.ajax({
            method:'POST',
            dataType:'json',
            url:'/passed.json',
            success:function(data){
                var passedPath = new google.maps.Polyline({
                    path: data,
                    geodesic: true,
                    strokeColor: '#FF0000',
                    strokeOpacity: 1.0,
                    strokeWeight: 2
                });

                passedPath.setMap(map);

                $.ajax({
                    method:'POST',
                    dataType:'json',
                    url:'/vehicle.json',
                    success:function(vehicleData){
                        var vehiclePath = new google.maps.Polyline({
                            path: vehicleData,
                            geodesic: true,
                            strokeColor: '#00FF00',
                            strokeOpacity: 1.0,
                            strokeWeight: 2
                        });

                        vehiclePath.setMap(map);

                        $.ajax({
                            method:'POST',
                            dataType:'json',
                            url:'/returned.json',
                            success:function(returnData){
                                var returnedPath = new google.maps.Polyline({
                                    path: returnData,
                                    geodesic: true,
                                    strokeColor: '#0000FF',
                                    strokeOpacity: 1.0,
                                    strokeWeight: 2
                                });

                                returnedPath.setMap(map);
                            }
                        })
                    }
                })
            }
        })
      }
    </script>
    <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDwcGALDxWC1T-5fnGvlzxvIJIoghO0ZUc&callback=initMap">
    </script>
  </body>
</html>
