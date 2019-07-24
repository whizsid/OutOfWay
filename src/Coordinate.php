<?php

namespace WhizSid\OutOfWay;

use JsonSerializable;

class Coordinate implements JsonSerializable {
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

    /**
     * Id for the given coordinate
     *
     * @var mixed
     */
    protected $id;

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

    /**
     * Returning the value of first eccentricity to the power two
     *
     * @link https://www.researchgate.net/publication/30871852_CONVERSION_OF_GPS_DATA_TO_CARTESIAN_COORDINATES_VIA_AN_APPLICATION_DEVELOPMENT_ADAPTED_TO_A_CAD_MODELLING_SYSTEM
     * 
     * @return float
     */
    protected function getValueOfSquareEccentricity(){
        return (pow(OOW_EARTH_MAJOR_RADIUS,2)-pow(OOW_EARTH_MINOR_RADIUS,2))/pow(OOW_EARTH_MAJOR_RADIUS,2);
    }

    /**
     * Returning the radius of curvature in the prime vertical
     *
     * @link https://www.researchgate.net/publication/30871852_CONVERSION_OF_GPS_DATA_TO_CARTESIAN_COORDINATES_VIA_AN_APPLICATION_DEVELOPMENT_ADAPTED_TO_A_CAD_MODELLING_SYSTEM
     * 
     * @return float
     */
    protected function getValueOfN(){
        $lat = $this->latitude;

        $sinLat = sin(\deg2rad($lat));

        $base = 1 - $this->getValueOfSquareEccentricity()*pow($sinLat,2);

        return OOW_EARTH_MAJOR_RADIUS/\sqrt($base);
    }

    /**
     * Returning the X coordinate for the given geo point
     *
     * @return float
     */
    public function getXCoordinate(){
        return $this->getValueOfN()*cos(\deg2rad($this->latitude))*cos(deg2rad($this->longitude));
    }

    /**
     * Returning the Y coordinate for the given geo point
     *
     * @return float
     */
    public function getYCoordinate(){
        return $this->getValueOfN()*cos(\deg2rad($this->latitude))*sin(deg2rad($this->longitude));
    }

    /**
     * Returning the Z coordinate for the given geo point
     *
     * @return float
     */
    public function getZCoordinate(){
        return $this->getValueOfN()*(1-pow($this->getValueOfSquareEccentricity(),2))*sin(\deg2rad($this->latitude));
    }
    
    /**
    * Setting position by X,Y,Z coordinates
    *
    * @param float $x
    * @param float $y
    * @param float $z
    *
    * @return void
    */
    public function setCoordinates($x,$y,$z){
        $this->longitude =rad2deg(atan($y/$x));
        
        $this->latitude = rad2deg(atan($z*sin(deg2rad($this->longitude))/((1-pow($this->getValueOfSquareEccentricity(),2))*$y)));
                
    }

    /**
     * Setting a ID for easy identication
     *
     * @param mixed $id
     * 
     * @return void
     */
    public function setId($id){
        $this->id = $id;
    }

    /**
     * Returning the ID
     * 
     * @return mixed
     *
     * @return void
     */
    public function getId(){

    }

    public function jsonSerialize()
    {
        return [
            'lat'=>$this->latitude,
            'lng'=>$this->longitude
        ];
    }
}
    