<?php

namespace WhizSid\OutOfWay;

/**
 * Helper fot out of way.
 */
class Helper
{
    /**
     * Parsing coordinates by an array.
     *
     * Array should be mutidimentional and have 'lat', 'lng' keys for each coordinate
     *
     * <code>
     *  $arr = [
     *      [
     *          'lat'=>6.34343,
     *          'lng'=>80.34343
     *      ]
     * ]
     * </code>
     *
     * @param array $coords
     *
     * @return Coordinate[]
     */
    public static function parseCoordinates($coords)
    {
        return array_map(function ($coord, $key) {
            $coordinate = new Coordinate($coord['lat'], $coord['lng']);

            $coordinate->setId(isset($coord['id']) ? $coord['id'] : $key);

            return $coordinate;
        }, $coords, array_keys($coords));
    }
}
