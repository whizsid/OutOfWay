<?php
require_once '../vendor/autoload.php';

use WhizSid\OutOfWay\OutOfWay;
use WhizSid\OutOfWay\VehicleCoordinate;

$passed = json_decode(file_get_contents('passed.json'),true);
$vehicle = json_decode(file_get_contents('vehicle.json'),true);

$outOfWay = new OutOfWay;

$outOfWay->setFilteration(false);

$formatedVehicle  = [];
$formatedPassed = [];

foreach($vehicle as $vehicleCoordinate){
    $formatedVehicle[] = new VehicleCoordinate($vehicleCoordinate['lat'],$vehicleCoordinate['lng']);
}

foreach($passed as $passedCoordinate){
    $formatedPassed[] = new VehicleCoordinate($passedCoordinate['lat'],$passedCoordinate['lng']);
}

$outOfWay->setActualCoordinates($formatedPassed);
$outOfWay->setVehicleCoordinates($formatedVehicle);

var_dump($outOfWay->getMatchedCoordinates());
