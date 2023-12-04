<?php

/**
 * Calculates the total points from a pile of scratchcards.
 *
 * @param string $filePath Path to the file containing the Scratchcard data.
 * @return int Total points from all scratchcards.
 */
function calculateTotalPoints(string $filePath): int
{

    $scratchcards = file($filePath, FILE_IGNORE_NEW_LINES);
    $totalPoints = 0;

    foreach ($scratchcards as $card) {
        // Split the card data into winning numbers and your numbers
        $parts = explode('|', $card);
        $winningNumbersPart = trim(substr($parts[0], strpos($parts[0], ':') + 1));
        $yourNumbersPart = trim($parts[1]);

        // Split into individual numbers
        $winningNumbers = explode(' ', preg_replace('/\s+/', ' ', $winningNumbersPart));
        $yourNumbers = explode(' ', preg_replace('/\s+/', ' ', $yourNumbersPart));

        $matches = array_intersect($winningNumbers, $yourNumbers);
        $matchCount = count($matches);

        // Calculate points based on the count of matches
        $points = $matchCount > 0 ? pow(2, $matchCount - 1) : 0;

        $totalPoints += $points;
    }

    return $totalPoints;
}

$filePath = 'input.txt';
echo "Total points: " . calculateTotalPoints($filePath) . "\n";