
<p align="center"><img src="https://i.imgur.com/fr2hWkb.png"></p>

---

<p align="center">
<a href="https://opensource.org/licenses/MIT"><img src="https://img.shields.io/badge/License-MIT-brightgreen.svg" alt="License: MIT"></a>
 <a href="https://github.styleci.io/repos/194457139"><img src="https://github.styleci.io/repos/194457139/shield?branch=master" alt="StyleCI"></a>
</p>

OutOfWay is a map matching library written in PHP. This package will returning matched coordinates when you passed  road coordinates and real coordinates.

![OutOfWay Map Matching Algorythm Output](https://i.imgur.com/wdV3oWu.png)

## Installation

You can install it with composer package manager.

```

composer require whizsid/outofway

```

## Usage

OutOfWay is easy to use. Only you want is call three methods.

```
// Creating new OutOfWay instance
$outOfWay = new OutOfWay;

// Setting the actual coordinates of the area.
$outOfWay->setActualCoordinates($outOfWay::$helper::parseCoordinates($passed));

// Passing error GPS coordinates
$outOfWay->setVehicleCoordinates($outOfWay::$helper::parseCoordinates($vehicle));

// Getting matched coordinates
$outOfWay->getMatchedCoordinates();

```

This `getMatchedCoordinate()` method will return an array of `Coordinate` instances. You can use `getLatitude`,`getLongitude` methods to retrieve latitude and longitude from a `Coordinate`

```
$lat = $coord->getLatitude();
$lng = $coord->getLongitude()
```

## Run Demo

1. Clone the project.

```
git clone https://github.com/whizsid/OutOfWay.git

```

2. Go to the `example` directory

```
cd example

```
3. Install depedencies.

```
composer install
```

4. Run local serve in it and access from web browser.

```
php -S 127.0.0.1:8000
```

## TODO

1. Filtering vehicle coordinates that have more errors.
2. Checking tangent when sorting coordinates from distance.
