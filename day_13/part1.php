<?php

const FILEPATH = 'input.txt';
const Y_REFLECTION_FACTOR = 100;

/**
 * Prepares the file content by replacing certain characters with specific values.
 *
 * @param string $filePath The path to the file.
 *
 * @return string The prepared content of the file with replaced characters.
 */
function prepareFileContent(string $filePath): string
{
    $content = file_get_contents($filePath);
    $content = str_replace(".", 0, $content);
    return str_replace("#", 1, $content);
}

/**
 * Calculates the reflection summary for the provided prepared content.
 *
 * @param string $preparedContent The prepared content containing pattern blocks.
 *
 * @return int The total reflection score for all pattern blocks in the prepared content.
 */
function calculateReflectionSummary(string $preparedContent): int
{
    $total = 0;
    $patterns = preg_split('/\n\s*\n/', trim($preparedContent));

    foreach ($patterns as $patternBlock) {
        $patternBlock = explode("\n", trim($patternBlock));
        $patternBlock = array_map('str_split', $patternBlock);
        $total += calculatePatternBlockScore($patternBlock);
    }

    return $total;
}

/**
 * Calculates the score of a given pattern block.
 *
 * @param array $patternBlock The pattern block for which the score is calculated.
 *
 * @return int The score of the pattern block.
 *
 * @throws RuntimeException If the number of x and y reflections is not equal to 1.
 */
function calculatePatternBlockScore(array $patternBlock): int
{
    $reflections = calculateReflections($patternBlock);
    return calculateTotalReflectionScore($reflections);
}


/**
 * Calculates the total reflection score based on the given reflection scores.
 *
 * @param array $reflectionScores The reflection scores.
 * @return int The total reflection score.
 */
function calculateTotalReflectionScore(array $reflectionScores): int
{
    $totalScore = 0;

    foreach ($reflectionScores['x'] as $xValue) {
        $totalScore += $xValue;
    }

    foreach ($reflectionScores['y'] as $yValue) {
        $totalScore += $yValue * Y_REFLECTION_FACTOR;
    }

    return $totalScore;
}

/**
 * Calculate the reflections for each axis based on the given pattern block.
 *
 * @param array $patternBlock An array representing the pattern block.
 *
 * @return array An associative array containing the reflections.
 *               The 'x' key will have an array of x reflections,
 *               and the 'y' key will have an array of y reflections.
 */
function calculateReflections(array $patternBlock): array
{
    $xReflections = calculateXReflections($patternBlock);
    $yReflections = calculateYReflections($patternBlock);
    return ['x' => $xReflections, 'y' => $yReflections];
}

/**
 * Calculates the reflection points for a given pattern block along the x-axis.
 *
 * @param int $blockDimension The dimension of the pattern block.
 * @param callable $checkSymmetryFunction The function to check for symmetry.
 * @param array $patternBlock The pattern block to calculate reflections for.
 *
 * @return array An array containing the reflection points along the x-axis.
 */
function calculateSymmetryReflections(int $blockDimension, callable $checkSymmetryFunction, array $patternBlock): array
{
    $sets = [];
    for ($point = 1; $point < $blockDimension; ++$point) {
        if ($checkSymmetryFunction($point, $patternBlock)) {
            $sets[] = $point;
        }
    }
    return $sets;
}


/**
 * Calculates the X reflections based on the given pattern block.
 *
 * @param array $patternBlock The pattern block to calculate the reflections for.
 * @return array The X reflections.
 */
function calculateXReflections(array $patternBlock): array
{
    return calculateSymmetryReflections(count($patternBlock[0]), 'checkPatternForVerticalSymmetry', $patternBlock);
}

/**
 * Calculate the y reflections based on the given pattern block.
 *
 * @param array $patternBlock An array representing the pattern block.
 *
 * @return array An array of y reflections calculated by calling the function
 *               calculateSymmetryReflections with the height of the pattern block,
 *               the 'checkPatternForHorizontalSymmetry' function name,
 *               and the pattern block as parameters.
 */
function calculateYReflections(array $patternBlock): array
{
    return calculateSymmetryReflections(count($patternBlock), 'checkPatternForHorizontalSymmetry', $patternBlock);
}


/**
 * Checks if a pattern block has vertical symmetry based on a reflection point.
 *
 * @param int $reflectionPoint The reflection point where the vertical symmetry is checked.
 * @param array $patternBlock The pattern block to check for vertical symmetry.
 * @return bool Returns true if the pattern block has vertical symmetry, otherwise false.
 */
function checkPatternForVerticalSymmetry(int $reflectionPoint, array $patternBlock): bool
{
    $blockHeight = count($patternBlock);
    $blockWidth = count($patternBlock[0]);

    // Check each of the X positions before the reflection point
    for ($x = 0; $x < $reflectionPoint; ++$x) {
        // Calculate reflection position on left side of reflection point
        $leftReflectionPos = $reflectionPoint - $x - 1;

        // Calculate reflection position on right side of reflection point
        $rightReflectionPos = $reflectionPoint + $x;

        // Ignore if it's outside of the pattern block width
        if ($leftReflectionPos < 0 || $rightReflectionPos >= $blockWidth)
            continue;

        // Check each Y position in the pattern block
        for ($y = 0; $y < $blockHeight; ++$y) {
            // Return false if the reflected positions are not symmetrical
            if ($patternBlock[$y][$leftReflectionPos] !== $patternBlock[$y][$rightReflectionPos])
                return false;
        }
    }

    // Return true if no symmetry issues were found
    return true;
}


/**
 * Check if a pattern block has horizontal symmetry.
 *
 * @param int $midLineIndex The index of the middle line in the pattern block.
 * @param array $patternBlock The pattern block represented as a 2-dimensional array of elements.
 *                            Each inner array represents a line in the pattern block.
 *                            The elements of the inner arrays represent the elements in each line.
 *
 * @return bool Returns true if the pattern block has horizontal symmetry, false otherwise.
 */
function checkPatternForHorizontalSymmetry(int $midLineIndex, array $patternBlock): bool
{
    $blockHeight = count($patternBlock);
    $blockWidth = count($patternBlock[0]);

    for ($lineIndex = 0; $lineIndex < $midLineIndex; ++$lineIndex) {
        $topLineIndex = $midLineIndex - $lineIndex - 1;
        $bottomLineIndex = $midLineIndex + $lineIndex;

        if ($topLineIndex < 0 || $bottomLineIndex >= $blockHeight)
            continue;

        if (!isSymmetryOnLine($blockWidth, $topLineIndex, $bottomLineIndex, $patternBlock))
            return false;
    }

    return true;
}

/**
 * Check if a given pattern demonstrates symmetry on a specified line.
 *
 * @param int $width The width of the pattern block.
 * @param int $topLine The index of the top line to compare.
 * @param int $bottomLine The index of the bottom line to compare.
 * @param array $patternBlock The pattern block to check for symmetry.
 *
 * @return bool Returns true if the pattern demonstrates symmetry on the specified line,
 *              otherwise returns false.
 */
function isSymmetryOnLine(int $width, int $topLine, int $bottomLine, array $patternBlock): bool
{
    for ($x = 0; $x < $width; ++$x) {
        $topLinePattern = $patternBlock[$topLine][$x];
        $bottomLinePattern = $patternBlock[$bottomLine][$x];

        if ($topLinePattern !== $bottomLinePattern) {
            return false;
        }
    }
    return true;
}

echo "Grand Total: " . calculateReflectionSummary(prepareFileContent(FILEPATH)) . PHP_EOL;
