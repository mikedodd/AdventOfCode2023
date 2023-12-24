<?php
const IN_BOUNDS = [200000000000000, 400000000000000];
const INPUT = "input.txt";

/**
 * Calculate the intersection point of two lines in a 2D plane.
 *
 * @param int $line1StartX The x-coordinate of the starting point of the first line.
 * @param int $line1StartY The y-coordinate of the starting point of the first line.
 * @param int $line1EndX The x-coordinate of the ending point of the first line.
 * @param int $line1EndY The y-coordinate of the ending point of the first line.
 * @param int $line2StartX The x-coordinate of the starting point of the second line.
 * @param int $line2StartY The y-coordinate of the starting point of the second line.
 * @param int $line2EndX The x-coordinate of the ending point of the second line.
 * @param int $line2EndY The y-coordinate of the ending point of the second line.
 * @return array|null The intersection point as an associative array with keys 'x' and 'y',
 *                    or null if the lines are parallel or coincident.
 */
function intersect(int $line1StartX, int $line1StartY, int $line1EndX, int $line1EndY, int $line2StartX, int $line2StartY, int $line2EndX, int $line2EndY)
{
    $denominator = ($line2EndY - $line2StartY) * ($line1EndX - $line1StartX) - ($line2EndX - $line2StartX) * ($line1EndY - $line1StartY);

    if ($denominator == 0) {
        return null;
    }

    $calculationParamA = (($line2EndX - $line2StartX) * ($line1StartY - $line2StartY) - ($line2EndY - $line2StartY) * ($line1StartX - $line2StartX)) / $denominator;

    return [
        'x' => $line1StartX + $calculationParamA * ($line1EndX - $line1StartX),
        'y' => $line1StartY + $calculationParamA * ($line1EndY - $line1StartY),
    ];
}


/**
 * Derives the line properties from the given line.
 *
 * @param string $line The
 */
function deriveLineProperties(string $line): array
{
    $properties = preg_split('/, | @ /', $line);
    $startX = (int)$properties[0];
    $endX = (int)$properties[3] + $startX;
    $startY = (int)$properties[1];
    $endY = (int)$properties[4] + $startY;

    return [$startX, $startY, $endX, $endY];
}

/**
 * Calculate the number of intersections between a set of lines in a 2D plane.
 *
 * @param array $inputLines An array of lines represented as arrays, each containing four elements representing the starting and ending points of a line.
 * @return int The number of intersections between the lines that are both in the future and in the defined bounds.
 */
function calculateIntersections(array $inputLines): int
{
    $intersectionCount = 0;

    for ($firstLineIndex = 0, $count = count($inputLines); $firstLineIndex < $count; $firstLineIndex++) {
        list($firstLineStartX, $firstLineStartY, $firstLineEndX, $firstLineEndY) = deriveLineProperties($inputLines[$firstLineIndex]);

        for ($secondLineIndex = $firstLineIndex + 1; $secondLineIndex < $count; $secondLineIndex++) {
            list($secondLineStartX, $secondLineStartY, $secondLineEndX, $secondLineEndY) = deriveLineProperties($inputLines[$secondLineIndex]);

            $intersection = intersect($firstLineStartX, $firstLineStartY, $firstLineEndX, $firstLineEndY, $secondLineStartX, $secondLineStartY, $secondLineEndX, $secondLineEndY);

            if ($intersection) {
                $intersectionX = intval($intersection['x']);
                $intersectionY = intval($intersection['y']);

                $isFutureEvent = isEventInFuture($intersectionX, $intersectionY, $firstLineStartX, $firstLineStartY, $firstLineEndX, $firstLineEndY, $secondLineStartX, $secondLineStartY, $secondLineEndX, $secondLineEndY);
                $isInBounds = ($intersectionX >= IN_BOUNDS[0] && $intersectionX <= IN_BOUNDS[1] && $intersectionY >= IN_BOUNDS[0] && $intersectionY <= IN_BOUNDS[1]);

                if ($isFutureEvent && $isInBounds) {
                    $intersectionCount++;
                }
            }
        }
    }
    return $intersectionCount;
}

/**
 * Check if an event, represented by an intersection point, is in the future.
 *
 * @param int $intersectionX The x-coordinate of the intersection point.
 * @param int $intersectionY The y-coordinate of the intersection point.
 * @param int $startX1 The x-coordinate of the starting point of the first line.
 * @param int $startY1 The y-coordinate of the starting point of the first line.
 * @param int $endX1 The x-coordinate of the ending point of the first line.
 * @param int $endY1 The y-coordinate of the ending point of the first line.
 * @param int $startX2 The x-coordinate of the starting point of the second line.
 * @param int $startY2 The y-coordinate of the starting point of the second line.
 * @param int $endX2 The x-coordinate of the ending point of the second line.
 * @param int $endY2 The y-coordinate of the ending point of the second line.
 * @return bool True if the intersection point is in the future, false otherwise.
 */
function isEventInFuture(int $intersectionX, int $intersectionY, int $startX1, int $startY1, int $endX1, int $endY1, int $startX2, int $startY2, int $endX2, int $endY2): bool
{
    return $intersectionX > $startX1 == $endX1 - $startX1 > 0 &&
        $intersectionY > $startY1 == $endY1 - $startY1 > 0 &&
        $intersectionX > $startX2 == $endX2 - $startX2 > 0 &&
        $intersectionY > $startY2 == $endY2 - $startY2 > 0;
}

$inputs = explode("\n", trim(file_get_contents(INPUT)));

$intersectionCount = calculateIntersections($inputs);

echo "$intersectionCount intersections occur within the test area\n";
