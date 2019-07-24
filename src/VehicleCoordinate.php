<?php

namespace WhizSid\OutOfWay;

class VehicleCoordinate extends Coordinate
{
    /**
     * Vehicles speed on current coordinate. This
     * parameter is required if filterCoordinates
     * is enabled.
     *
     * @var float
     */
    protected $speed;

    /**
     * Current time in micro seconds. Nt unix timestamp.
     *
     * @var int
     */
    protected $time;

    /**
     * Setting up the speed.
     *
     * @param float $speed
     *
     * @return void
     */
    public function setSpeed($speed)
    {
        $this->speed = $speed;
    }

    /**
     * Returning the speed in current coordinate.
     *
     * @return float
     */
    public function getSpeed()
    {
        return $this->speed;
    }

    /**
     * Setting the current time.
     *
     * @param int $time in micro seconds
     *
     * @return void
     */
    public function setCurrentTime($time)
    {
        $this->time = $time;
    }

    /**
     * Returning the current time.
     *
     * @return int
     */
    public function getCurrentTime()
    {
        return $this->time;
    }
}
