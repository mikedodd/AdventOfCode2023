<?php

/**
 * Class Brick
 *
 * Represents a brick object with dimensions defined by the coordinates of its edges.
 */
class Brick
{
    public $x1, $x2, $y1, $y2, $z1, $z2;

    public function __construct($x1, $x2, $y1, $y2, $z1, $z2)
    {
        $this->x1 = $x1;
        $this->x2 = $x2;
        $this->y1 = $y1;
        $this->y2 = $y2;
        $this->z1 = $z1;
        $this->z2 = $z2;
    }
}

/**
 * Parses the input lines and creates an array of brick objects.
 *
 * @param string[] $inputLines The input lines containing brick coordinates in the format "x1~y1~z1~x2~y2~z2"
 * @return Brick[] The array of brick objects created from the input lines
 */
function parseInput(array $inputLines): array
{
    $bricks = [];
    foreach ($inputLines as $line) {
        $coordinates = preg_split("/[~,]/", $line);
        $xMin = min($coordinates[0], $coordinates[3]);
        $xMax = max($coordinates[0], $coordinates[3]);
        $yMin = min($coordinates[1], $coordinates[4]);
        $yMax = max($coordinates[1], $coordinates[4]);
        $zMin = min($coordinates[2], $coordinates[5]);
        $zMax = max($coordinates[2], $coordinates[5]);

        $bricks[] = new Brick($xMin, $xMax, $yMin, $yMax, $zMin, $zMax);
    }
    return $bricks;
}

/**
 * Check if two bricks overlap.
 *
 * This function checks if two bricks overlap in a three-dimensional space.
 * The bricks are represented by the $brickArray parameter, which is an array of Brick objects.
 * The specific bricks to check are determined by their indices $brickIndexOne and $brickIndexTwo.
 *
 * @param Brick[] $brickArray An array of Brick objects representing the bricks.
 * @param int $brickIndexOne The index of the first brick to check.
 * @param int $brickIndexTwo The index of the second brick to check.
 *
 * @return bool True if the two bricks overlap, false otherwise.
 */
function checkBrickOverlap(array $brickArray, int $brickIndexOne, int $brickIndexTwo): bool
{
    $brickOne = $brickArray[$brickIndexOne];
    $brickTwo = $brickArray[$brickIndexTwo];
    return $brickIndexOne != $brickIndexTwo &&
        max($brickOne->x1, $brickTwo->x1) <= min($brickOne->x2, $brickTwo->x2) &&
        max($brickOne->y1, $brickTwo->y1) <= min($brickOne->y2, $brickTwo->y2) &&
        max($brickOne->z1, $brickTwo->z1) <= min($brickOne->z2, $brickTwo->z2);
}

/**
 * Check if there exists any overlap between a target brick and the rest of the bricks in an array.
 *
 * @param array $brickArray An array of Brick objects representing the bricks in the three-dimensional space.
 * @param int $targetBrickIndex The index of the target brick in the brick array.
 *
 * @return bool Returns true if an overlap exists, false otherwise.
 */
function doesOverlapExist(array $brickArray, int $targetBrickIndex): bool
{
    foreach ($brickArray as $currentBrickIndex => $currentBrick) {
        if (checkBrickOverlap($brickArray, $targetBrickIndex, $currentBrickIndex)) {
            return true;
        }
    }
    return false;
}

/**
 * Calculates the number of non-overlapping bricks in the given brick array
 *
 * @param array $brickArray The array of bricks
 * @return int The number of non-overlapping bricks
 */
function calculateBrickOverlap(array $brickArray): int
{
    do {
        usort($brickArray, function ($brickA, $brickB) {
            return $brickA->z1 <=> $brickB->z1 ?: $brickA->x1 <=> $brickB->x1 ?: $brickA->y1 <=> $brickB->y1;
        });
        $bricksRepositioned = false;
        foreach ($brickArray as $brickIndex => $brick) {
            while ($brick->z1 > 1) {
                $brick->z1--;
                if (doesOverlapExist($brickArray, $brickIndex)) {
                    $brick->z1++;
                    break;
                }
                $brick->z2--;
                $bricksRepositioned = true;
            }
        }
    } while ($bricksRepositioned);
    $resBrickCount = 0;
    foreach ($brickArray as $brickIndex => $brick) {
        $nextZCoordinate = $brick->z2 + 1;
        $brick->z2 -= 1000;
        $overlapFound = false;
        foreach ($brickArray as $innerIndex => $innerBrick) {
            if ($innerIndex > $brickIndex && $innerBrick->z1 == $nextZCoordinate) {
                $innerBrick->z1--;
                if (!doesOverlapExist($brickArray, $innerIndex)) {
                    $overlapFound = true;
                    continue;
                }
                $innerBrick->z1++;
            }
        }
        if (!$overlapFound) {
            $resBrickCount++;
        }
        $brick->z2 += 1000;
    }
    return $resBrickCount;
}

$bricks = parseInput(file("input.txt"));
$results = calculateBrickOverlap($bricks);

echo "How many bricks could be safely chosen as the one to get disintegrated: " . $results . "\n";