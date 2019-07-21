<?php
namespace WhizSid\OutOfWay;

use WhizSid\OutOfWay\Coordinate;
use WhizSid\OutOfWay\VehicleCoordinate;

class OutOfWay
{

    /**
     * Array of coordinates received from the vehicle
     *
     * @var VehicleCoordinate[]
     */
    protected $vehicleCoordinates = [];

    /**
     * Actual coordinates received from the source
     *
     * @var Coordinate[]
     */
    protected $actualCoordinates = [];

    /**
     * Weather that enabled filtering the coordinates.
     * We are highly recomendating the filteration. Because
     * If received an error GPS coordinate whole path will
     * be changing
     *
     * @var bool
     */
    protected $enableFilteration = true;

    /**
     * Minimum relative error. Coordinates with relative error
     * than this will be removed in filterations.
     *
     * @var float
     */
    protected $error = OOW_MAX_RELATIVE_ERROR;

    /**
     * Earth radius in kilo meters. We are calculating all distances
     * using this as the earth radius
     *
     * @var integer
     */
    protected $earthRadius = OOW_EARTH_AVERAGE_RADIUS ;

    /**
     * Setting the vehicle coordinates
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
     * Setting the actual coordinates received from a source
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
     * Setting the filteration
     *
     * @param boolean $active
     *
     * @return void
     */
    public function setFilteration($active = true)
    {
        $this->enableFilteration = $active;
    }

    /**
     * Return the weather tha filteration is on or not
     *
     * @return boolean
     */
    public function isFiltering()
    {
        return $this->enableFilteration;
    }

    /**
     * Maximum relative error that can be happened
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
     * Returning the maximum relative error
     *
     * @return float
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Filtering coordinates and removing error coordinates
     * 
     * @link https://medium.com/driving-to-the-future/map-matching-and-the-processing-of-raw-gps-data-on-an-industrial-scale-599a9475d332
     *
     * @return void
     */
    protected function filterCoordinates()
    {
        $this->vehicleCoordinates = array_filter($this->vehicleCoordinates, function ( VehicleCoordinate $coordinate,$i) {
            if($i==0)
                return true;

            $gpsVelocity = $this->calculateDistance(
                $this->vehicleCoordinates[$i-1]->getLatitude(),
                $this->vehicleCoordinates[$i-1]->getLongitude(),
                $coordinate->getLatitude(),
                $coordinate->getLongitude(),
                $this->earthRadius
            )/(
                ($this->vehicleCoordinates[$i-1]->getCurrentTime()
                -
                $coordinate->getCurrentTime())/(1000*1000*60*60)
            );

            $vehicleVelocity = (
                $this->vehicleCoordinates[$i-1]->getSpeed()
                +
                $coordinate->getSpeed()
            )/2;

            $relativeError = abs($gpsVelocity-$vehicleVelocity)/$vehicleVelocity;

            if($relativeError>$this->getError()){
                return false;
            }

            return true;
            
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Calculates the great-circle distance between two points, with
     * the Vincenty formula.
     * 
     * @link https://stackoverflow.com/a/10054282/5498631
     * 
     * @param float $latitudeFrom Latitude of start point in [deg decimal]
     * @param float $longitudeFrom Longitude of start point in [deg decimal]
     * @param float $latitudeTo Latitude of target point in [deg decimal]
     * @param float $longitudeTo Longitude of target point in [deg decimal]
     * @param float $earthRadius Mean earth radius in [km]
     * 
     * @return float Distance between points in [km] (same as earthRadius)
     */
    protected function calculateDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = OOW_EARTH_AVERAGE_RADIUS) {
        // convert from degrees to radians
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $lonDelta = $lonTo - $lonFrom;
        $a = pow(cos($latTo) * sin($lonDelta), 2) +
        pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta), 2);
        $b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);

        $angle = atan2(sqrt($a), $b);
        return $angle * $earthRadius;
    }
    
    protected function getVehiclePosition($coord1,$coord2,$vehicle){
    	$x1 = $coord1->getXCoordinate();
    	$x2 = $coord2->getXCoordinate();
    	$x3 = $vehicle->getXCoordinate();
    	
    	$y1 = $coord1->getYCoordinate();
    	$y2 = $coord2->getYCoordinate();
    	$y3 = $vehicle->getYCoordinate();
    	
    	$z1 = $coord1->getZCoordinate();
    	$z2 = $coord2->getZCoordinate();
    	$z3 = $vehicle->getZCoordinate();
    	
    	$coordinate = new Coordinate;
    	
        $numerator = ( pow($x2- $x1,2) + pow($y2-$y1,2) );
    	
    	$x= ( ($x2-$x1) * ( $x3*($x2-$x1) + $y3*($y2-$y1) ) + ($y2-$y1) * ($x1*$y2-$x2*$y1) )/ $numerator;
    		
    	$y = ( ($y2-$y1) * ( $x3*($x2-$x1) + $y3*($y2-$y1) ) + ($x2-$x1) * ($x1*$y2-$x2*$y1) )/ $numerator;
    	
    	$z= ( ($z2-$z1) * ( $z3*($z2-$z1) + $y3*($y2-$y1) ) + ($y2-$y1) * ($z1*$y2-$z2*$y1) )/ $numerator;
    		
    	$coordinate->setCoordinates($x,$y,$z);
    	
    	return $coordinate;
    	
    }

    /**
    * Executing the GPS data
    *
    * @return Coordinate[]]
    */
    public function getMatchedCoordinates(){
    	
    	if($this->enableFilteration){
    		$this->filterCoordinates();
    	}

    	$vehicle = $this->vehicleCoordinates;
    	$actual = $this->actualCoordinates;
    	$matched=[];
    	
    	
    	foreach($vehicle as $vehicleCoordinate){
    		$sorted = array_sort($actual, function(Coordinate $actual)use(VehicleCoordinate $vehicle)){
    			return $this->calculateDistance(
    				$vehicle->getLatitude(),
    				$vehicle->getLongitude(),
    				$actual->getLantitude(),
    				$actual->getLongitude()
    			);
    		});
    		
    		$first=$sorted[0];
    		$second=$sorted[1];
    		
    		$position = $this->getVehiclePosition($first,$second,$vehicle);
    		$matched[]= $position
    	}
    	
    	return $matched;
    }
}
    
    
    
    
