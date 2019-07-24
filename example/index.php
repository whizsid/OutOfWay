<?php
require_once '../vendor/autoload.php';

use WhizSid\OutOfWay\OutOfWay;

$passed = json_decode(file_get_contents('passed.json'), true);
$vehicle = json_decode(file_get_contents('vehicle.json'), true);

$outOfWay = new OutOfWay;

$outOfWay->setActualCoordinates($outOfWay::$helper::parseCoordinates($passed));
$outOfWay->setVehicleCoordinates($outOfWay::$helper::parseCoordinates($vehicle));

$contents = json_encode($outOfWay->getMatchedCoordinates());

file_put_contents('returned.json', $contents);

?>
<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <title>OutOfWay Demo</title>
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
        <span class="color blue"></span> Actual Road  <span class="color green"></span> Matched Coordinates <span class="color red" ></span> Real Coordinates
    </div>
    <script>

      function initMap() {
        var map = new google.maps.Map(document.getElementById('map'), {
          zoom: 12,
          center: {
                "lat":6.88020,
                "lng":80.24290
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
                    strokeColor: '#0000BB',
                    strokeOpacity: 1.0,
                    strokeWeight: 2
                });

                passedPath.setMap(map);

                $.ajax({
                    method:'POST',
                    dataType:'json',
                    url:'/vehicle.json',
                    success:function(vehicleData){

                        vehicleData.forEach((data,key) => {
                          var marker = new google.maps.Marker({
                            position: data,
                            map: map,
                            icon:'https://chart.googleapis.com/chart?chst=d_map_pin_letter&chld=' + key +'|FF0000|000000',
                            title: 'Vehicle Position'
                          });
                        });

                        $.ajax({
                            method:'POST',
                            dataType:'json',
                            url:'/returned.json',
                            success:function(returnData){
                                
                              returnData.forEach((data,key) => {
                                var marker = new google.maps.Marker({
                                  position: data,
                                  icon:'https://chart.googleapis.com/chart?chst=d_map_pin_letter&chld=' + key +'|00FF00|000000',
                                  map: map,
                                  title: 'Calculated Position'
                                });
                              });
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
