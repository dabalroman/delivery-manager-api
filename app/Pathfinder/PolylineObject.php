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
    public function __construct(array $pointsArray)
    {
        $this->polyline = $pointsArray;
    }

    /**
     * @param int $tolerance
     */
    public function simplify($tolerance = -1)
    {
        if ($tolerance == -1) {
            $tolerance = env('POLYLINE_SIMPLIFIER_TOLERANCE');
        }


    }

    /**
     * @param PolylineObject $polyline
     * @throws Exception
     */
    public function joinAfter(PolylineObject $polyline)
    {
        if (last($this->polyline) !== $polyline->polyline[0]) {
            throw new Exception('Different meet points');
        }

        foreach ($polyline as $point) {
            array_push($this->polyline, $point);
        }
    }

    /**
     * @param string $encodedPolyline
     * @return PolylineObject
     */
    static public function fromEncoded(string $encodedPolyline): PolylineObject
    {
        $decoded = Polyline::pair(Polyline::decode($encodedPolyline));
        return new PolylineObject($decoded);
    }

    /**
     * @return string Encoded polyline
     */
    public function encode(): string
    {

    }
}
