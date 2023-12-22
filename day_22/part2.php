<?php

/**
 * Class Brick
 *
 * The Brick class represents a brick in a three-dimensional space.
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
 * Calculate the number of fallen bricks in a brick collection.
 *
 * This function iterates over the given brick collection and determines the number of bricks that have fallen.
 * A brick is considered fallen if it does not overlap with any other bricks in the collection.
 *
 * @param array $brickCollection An array of Brick objects representing the brick collection.
 * @return int The number of fallen bricks in the collection.
 */
function calculateBrickOverlap(array $brickCollection): int
{
    do {
        usort($brickCollection, function ($firstBrick, $secondBrick) {
            return $firstBrick->z1 <=> $secondBrick->z1 ?: $firstBrick->x1 <=> $secondBrick->x1 ?: $firstBrick->y1 <=> $secondBrick->y1;
        });
        $isChanged = false;
        foreach ($brickCollection as $brickIndex => $currentBrick) {
            while ($currentBrick->z1 > 1) {
                $currentBrick->z1--;
                if (doesOverlapExist($brickCollection, $brickIndex)) {
                    $currentBrick->z1++;
                    break;
                }
                $currentBrick->z2--;
                $isChanged = true;
            }
        }
    } while ($isChanged);

    $fallenBricksCount = 0;

    foreach ($brickCollection as $brickIndex => $currentBrick) {
        $currentBrick->z2 -= 1000;
        $localFallenBricks = 0;
        foreach ($brickCollection as $innerBrickIndex => $innerBrick) {
            if ($innerBrickIndex !== $brickIndex && $innerBrick->z1 > 1) {
                $innerBrick->z1--;
                if (!doesOverlapExist($brickCollection, $innerBrickIndex)) {
                    $innerBrick->z2 -= 1000;
                    $localFallenBricks++;
                }
                $innerBrick->z1++;
            }
        }
        foreach ($brickCollection as $innerBrick) {
            if ($innerBrick->z2 < 0) {
                $innerBrick->z2 += 1000;
            }
        }
        $fallenBricksCount += $localFallenBricks;
    }
    return $fallenBricksCount;
}

$bricks = parseInput(file("input.txt"));
$results = calculateBrickOverlap($bricks);

echo "The sum of the number of other bricks that would fall: " . $results . "\n";