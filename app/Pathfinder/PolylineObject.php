<?php


namespace App\Pathfinder;


use Exception;
use Polyline;

class PolylineObject
{
    private array $polyline;

    /**
     * Polyline constructor.
     *
     * @param float[] $pointsArray Array of coordinates ([[x, y], [r, q], ...])
     */
    public function __construct(array $pointsArray = [])
    {
        $this->polyline = $pointsArray;
    }

    /**
     * Create pllObj from encoded string
     *
     * @param string $encodedPolyline
     * @return PolylineObject
     */
    static public function fromEncoded(string $encodedPolyline): PolylineObject
    {
        $decoded = Polyline::pair(Polyline::decode($encodedPolyline));
        return new PolylineObject($decoded);
    }

    /**
     * @param int $tolerance
     */
    public function simplify($tolerance = -1)
    {
        if ($tolerance == -1) {
            $tolerance = env('POLYLINE_SIMPLIFIER_TOLERANCE');
        }

        $xyPolyline = array_map(function ($point) {
            return ['x' => $point[0], 'y' => $point[1]];
        }, $this->polyline);

        $xyPolyline = PolylineSimplifier::simplify($xyPolyline, $tolerance, true);

        $this->polyline = array_map(function ($point) {
            return [$point['x'], $point['y']];
        }, $xyPolyline);

        unset($xyPolyline);
    }

    /**
     * Add another polylineObject.
     * Last point from current pll and first point from new pll must be exactly the same.
     *
     * @param PolylineObject $polylineObject
     * @throws Exception
     */
    public function joinAfter(PolylineObject $polylineObject)
    {
        if (!empty($this->polyline) && last($this->polyline) !== $polylineObject->polyline[0]) {
            throw new Exception('Different outer points');
        }

        foreach ($polylineObject->polyline as $point) {
            array_push($this->polyline, $point);
        }
    }

    /**
     * @return string
     * @noinspection PhpUnusedLocalVariableInspection
     */
    public function __toString(): string
    {
        return
            count($this->polyline) . " points: \n"
            . array_reduce($this->polyline, function ($accumulator, $current) {
                return $accumulator .= $current[0] . ', ' . $current[1] . "\n";
            }, "");
    }

    /**
     * Returns encoded polyline
     *
     * @return string Encoded polyline
     */
    public function encode(): string
    {
        return Polyline::encode($this->polyline);
    }
}
