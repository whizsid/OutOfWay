<?php

namespace WhizSid\OutOfWay;

class Coordinate {
    /**
     * Latitude of the coordinate
     *
     * @var float
     */
    protected $latitude;

    /**
     * Longitude of the coordinate
     *
     * @var float
     */
    protected $longitude;

    public function __construct($lat=null,$lng=null)
    {
        if(isset($lat))
            $this->latitude = $lat;
        
        if(isset($lng))
            $this->longitude = $lng;
    }

    /**
     * Setting the latitude
     *
     * @param float $lat
     * 
     * @return void
     */
    public function setLatitude($lat){
        $this->latitude = $lat;
    }

    /**
     * Returning the latitude for the current position
     *
     * @return float
     */
    public function getLatitude(){
        return $this->latitude;
    }

    /**
     * Setter for the longitude
     *
     * @param float $lng
     * 
     * @return void
     */
    public function setLongitude($lng){
        $this->longitude = $lng;
    }

    /**
     * Returning the longitude for the current position
     *
     * @return float
     */
    public function getLongitude(){
        return $this->longitude;
    }

}