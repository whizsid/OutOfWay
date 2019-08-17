<?php

namespace WhizSid\OutOfWay;

class OutOfWay
{
    /**
     * Helper for the out of way.
     *
     * @var Helper
     */
    public static $helper = Helper::class;

    /**
     * Array of coordinates received from the vehicle.
     *
     * @var VehicleCoordinate[]
     */
    protected $vehicleCoordinates = [];

    /**
     * Actual coordinates received from the source.
     *
     * @var Coordinate[]
     */
    protected $actualCoordinates = [];

    /**
     * Weather that enabled filtering the coordinates.
     * We are highly recomendating the filteration. Because
     * If received an error GPS coordinate whole path will
     * be changing.
     *
     * @var bool
     */
    protected $enableFilteration = false;

    /**
     * Minimum relative error. Coordinates with relative error
     * than this will be removed in filterations.
     *
     * @var float
     */
    protected $error = OOW_MAX_RELATIVE_ERROR;

    /**
     * Earth radius in kilo meters. We are calculating all distances
     * using this as the earth radius.
     *
     * @var int
     */
    protected $earthRadius = OOW_EARTH_AVERAGE_RADIUS;

    /**
     * Setting the vehicle coordinates.
     *
     * @param VehicleCoordinate[] $coordinates
     *
     * @return void
     */
    public function setVehicleCoordinates($coordinates)
    {
        $this->vehicleCoordinates = $coordinates;
    }

    /**
     * Setting the actual coordinates received from a source.
     *
     * @param Coordinate[] $coordinates
     *
     * @return void
     */
    public function setActualCoordinates($coordinates)
    {
        $this->actualCoordinates = $coordinates;
    }

    /**
     * Setting the filteration.
     *
     * @param bool $active
     *
     * @return void
     */
    public function setFilteration($active = true)
    {
        $this->enableFilteration = $active;
    }

    /**
     * Return the weather tha filteration is on or not.
     *
     * @return bool
     */
    public function isFiltering()
    {
        return $this->enableFilteration;
    }

    /**
     * Maximum relative error that can be happened.
     *
     * @param float $err
     *
     * @return void
     */
    public function setError($err)
    {
        $this->error = $err;
    }

    /**
     * Returning the maximum relative error.
     *
     * @return float
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Filtering coordinates and removing error coordinates.
     *
     * @link https://medium.com/driving-to-the-future/map-matching-and-the-processing-of-raw-gps-data-on-an-industrial-scale-599a9475d332
     *
     * @return void
     */
    protected function filterCoordinates()
    {
        $this->vehicleCoordinates = array_filter($this->vehicleCoordinates, function (VehicleCoordinate $coordinate, $i) {
            if ($i == 0) {
                return true;
            }

            $gpsVelocity = $this->calculateDistance(
                $this->vehicleCoordinates[$i - 1]->getLatitude(),
                $this->vehicleCoordinates[$i - 1]->getLongitude(),
                $coordinate->getLatitude(),
                $coordinate->getLongitude(),
                $this->earthRadius
            ) / (
                ($this->vehicleCoordinates[$i - 1]->getCurrentTime()
                -
                $coordinate->getCurrentTime()) / (1000 * 1000 * 60 * 60)
            );

            $vehicleVelocity = (
                $this->vehicleCoordinates[$i - 1]->getSpeed()
                +
                $coordinate->getSpeed()
            ) / 2;

            $relativeError = abs($gpsVelocity - $vehicleVelocity) / $vehicleVelocity;

            if ($relativeError > $this->getError()) {
                return false;
            }

            return true;
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Calculates the distance of two coordinates.
     *
     * @param Coordinate $coord1
     * @param Coordinate $coord2
     *
     * @return float Distance between points in [km]
     */
    protected function calculateDistance($coord1, $coord2)
    {
        // convert from degrees to radians

        $x1 = $coord1->getXCoordinate();
        $x2 = $coord2->getXCoordinate();

        $y1 = $coord1->getYCoordinate();
        $y2 = $coord2->getYCoordinate();

        $z1 = $coord1->getZCoordinate();
        $z2 = $coord2->getZCoordinate();

        return sqrt(pow($x2 - $x1, 2) + pow($y2 - $y1, 2) + pow($z2 - $z1, 2));
    }

    /**
     * Returning the matched position for two coordinates and vehicle coordinate.
     *
     * @link https://math.stackexchange.com/a/3302374/686279
     *
     * @param Coordinate        $coord1
     * @param Coordinate        $coord2
     * @param VehicleCoordinate $vehicle
     *
     * @return Coordinate
     */
    protected function getVehiclePosition($coord1, $coord2, $vehicle)
    {
        $x1 = $coord1->getXCoordinate();
        $x2 = $coord2->getXCoordinate();
        $x3 = $vehicle->getXCoordinate();

        $y1 = $coord1->getYCoordinate();
        $y2 = $coord2->getYCoordinate();
        $y3 = $vehicle->getYCoordinate();

        $z1 = $coord1->getZCoordinate();
        $z2 = $coord2->getZCoordinate();
        $z3 = $vehicle->getZCoordinate();

        $coordinate = new Coordinate();

        $w = (($x1 - $x2) * ($x3 - $x2) + ($y1 - $y2) * ($y3 - $y2) + ($z1 - $z2) * ($z3 - $z2)) / (pow($x1 - $x2, 2) + pow($y1 - $y2, 2) + pow($z1 - $z2, 2));

        $x = $w * $x1 + (1 - $w) * $x2;
        $y = $w * $y1 + (1 - $w) * $y2;
        $z = $w * $z1 + (1 - $w) * $z2;

        $coordinate->setCoordinates($x, $y, $z);

        return $coordinate;
    }

    /**
     * Executing the GPS data.
     *
     * @return Coordinate[]]
     */
    public function getMatchedCoordinates()
    {
        if ($this->enableFilteration) {
            $this->filterCoordinates();
        }

        $vehicle = $this->vehicleCoordinates;
        $actual = $this->actualCoordinates;
        $matched = [];

        foreach ($vehicle as $vehicleCoordinate) {
            usort($actual, function (Coordinate $actual1, Coordinate $actual2) use ($vehicleCoordinate) {
                $d1 = $this->calculateDistance(
                    $vehicleCoordinate,
                    $actual1
                );

                $d2 = $this->calculateDistance(
                    $vehicleCoordinate,
                    $actual2
                );

                if ($d1 == $d2) {
                    return 0;
                }

                return ($d1 < $d2) ? -1 : 1;
            });

            $first = $actual[0];
            $second = $actual[1];

            $position = $this->getVehiclePosition($first, $second, $vehicleCoordinate);

            $matched[] = $position;
        }

        return $matched;
    }
}
