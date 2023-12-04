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
    $totalCards = count($scratchcards);

    for ($i = 0; $i < count($scratchcards); $i++) {
        $totalCards += processCardCopies($scratchcards, $i);

    }

    return $totalCards;
}

/**
 * Processes a single card and its copies recursively.
 *
 * @param array $scratchcards Array of all scratchcards.
 * @param int $cardIndex Index of the current scratchcard.
 * @return int Total number of copies generated by this card and its copies.
 */
function processCardCopies(array &$scratchcards, int $cardIndex): int {
    if ($cardIndex >= count($scratchcards)) {
        return 0;
    }

    [$winningNumbersPart, $yourNumbersPart] = explode('|', $scratchcards[$cardIndex]);
    $winningNumbers = array_filter(explode(' ', trim($winningNumbersPart)), 'strlen');
    $yourNumbers = array_filter(explode(' ', trim($yourNumbersPart)), 'strlen');

    $matches = array_intersect($winningNumbers, $yourNumbers);
    $matchCount = count($matches);

    // Calculate the total number of copies this card will generate
    $totalCopies = 0;
    for ($i = 1; $i <= $matchCount; $i++) {
        $totalCopies += 1 + processCardCopies($scratchcards, $cardIndex + $i);
    }

    return $totalCopies;
}

$filePath = 'input.txt';
echo "Total points: " . calculateTotalPoints($filePath) . "\n";
