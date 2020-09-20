<?php /** @noinspection PhpMissingDocCommentInspection */

namespace App\Pathfinder;

/**
 * Class PolylineSimplifier
 * Credits: https://github.com/AKeN/simplify-php
 *
 * @package App\Pathfinder
 */
class PolylineSimplifier
{
    /**
     * Polyline simplification dramatically reduces the number of points in a polyline while retaining its shape.
     *
     * @param array $points         [[x => 0, y => 1]]
     * @param int   $tolerance      Amount of simplification in same metric as point coordinates
     * @param false $highestQuality Excludes distance-based preprocessing
     * @return array Simplified polyline
     */
    public static function simplify($points, $tolerance = 1, $highestQuality = false)
    {
        if (count($points) < 2) return $points;
        $sqTolerance = $tolerance * $tolerance;
        if (!$highestQuality) {
            $points = PolylineSimplifier::simplifyRadialDistance($points, $sqTolerance);
        }
        $points = PolylineSimplifier::simplifyDouglasPeucker($points, $sqTolerance);

        return $points;
    }

    private static function simplifyRadialDistance($points, $sqTolerance)
    { // distance-based simplification

        $len = count($points);
        $prevPoint = $points[0];
        $newPoints = [$prevPoint];
        $point = null;


        for ($i = 1; $i < $len; $i++) {
            $point = $points[$i];

            if (PolylineSimplifier::getSquareDistance($point, $prevPoint) > $sqTolerance) {
                array_push($newPoints, $point);
                $prevPoint = $point;
            }
        }

        if ($prevPoint !== $point) {
            array_push($newPoints, $point);
        }

        return $newPoints;
    }

    private static function getSquareDistance($p1, $p2)
    {
        $dx = $p1['x'] - $p2['x'];
        $dy = $p1['y'] - $p2['y'];
        return $dx * $dx + $dy * $dy;
    }

    private static function simplifyDouglasPeucker($points, $sqTolerance)
    {

        $len = count($points);

        $markers = array_fill(0, $len - 1, null);
        $first = 0;
        $last = $len - 1;

        $firstStack = [];
        $lastStack = [];

        $newPoints = [];

        $markers[$first] = $markers[$last] = 1;

        while ($last) {

            $maxSqDist = 0;

            for ($i = $first + 1; $i < $last; $i++) {
                $sqDist = PolylineSimplifier::getSquareSegmentDistance($points[$i], $points[$first], $points[$last]);

                if ($sqDist > $maxSqDist) {
                    $index = $i;
                    $maxSqDist = $sqDist;
                }
            }

            if ($maxSqDist > $sqTolerance) {
                $markers[$index] = 1;

                array_push($firstStack, $first);
                array_push($lastStack, $index);

                array_push($firstStack, $index);
                array_push($lastStack, $last);
            }

            $first = array_pop($firstStack);
            $last = array_pop($lastStack);
        }

        for ($i = 0; $i < $len; $i++) {
            if ($markers[$i]) {
                array_push($newPoints, $points[$i]);
            }
        }

        return $newPoints;
    }


// simplification using optimized Douglas-Peucker algorithm with recursion elimination

    private static function getSquareSegmentDistance($p, $p1, $p2)
    {
        $x = $p1['x'];
        $y = $p1['y'];

        $dx = $p2['x'] - $x;
        $dy = $p2['y'] - $y;

        if ($dx !== 0 || $dy !== 0) {

            $t = (($p['x'] - $x) * $dx + ($p['y'] - $y) * $dy) / ($dx * $dx + $dy * $dy);

            if ($t > 1) {
                $x = $p2['x'];
                $y = $p2['y'];

            } else if ($t > 0) {
                $x += $dx * $t;
                $y += $dy * $t;
            }
        }

        $dx = $p['x'] - $x;
        $dy = $p['y'] - $y;

        return $dx * $dx + $dy * $dy;
    }


}
