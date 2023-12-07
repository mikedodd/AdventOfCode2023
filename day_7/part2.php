<?php


/**
 * Parses the hand data from a given file path and returns it as an array.
 *
 * @param string $filePath The path to the file containing the hand data.
 * @return array The parsed hand data as an associative array,
 *               where each element represents a hand and bid in the format ['hand' => $hand, 'bid' => $bid].
 */
function parseHandData(string $filePath): array
{
    $lines = file($filePath, FILE_IGNORE_NEW_LINES);
    $hands = [];

    foreach ($lines as $line) {
        list($hand, $bid) = explode(' ', $line);
        $hands[] = ['hand' => $hand, 'bid' => (int)$bid];
    }

    return $hands;
}

/**
 * Retrieves the value of a given playing card.
 *
 * @param string $card The card to retrieve the value for. Valid card values are: '2', '3', '4', '5', '6', '7', '8', '9', 'T', 'J', 'Q', 'K', 'A'.
 * @return int The numerical value of the card. Returns null if the card is invalid.
 */
function cardValue($card): int
{
    $values = ['2' => 2, '3' => 3, '4' => 4, '5' => 5, '6' => 6, '7' => 7, '8' => 8, '9' => 9, 'T' => 10, 'J' => 1, 'Q' => 12, 'K' => 13, 'A' => 14];
    return $values[$card];
}

/**
 * Determines the rank of a poker hand based on the given string representation of the hand.
 *
 * @param string $hand The string representation of the hand.
 * @return int The rank of the hand.
 */
function handRank(string $hand): int
{
    // Count the occurrences of each card
    $counts = array_count_values(str_split($hand));
    arsort($counts); // Sort by frequency and value
    $types = ['Five of a kind' => 9, 'Four of a kind' => 8, 'Full house' => 7, 'Three of a kind' => 6, 'Two pair' => 5, 'One pair' => 4, 'High card' => 3];

    $numberOfJokers = $counts['J'] ?? 0;
    unset($counts['J']);


    if(!$counts){
        return $types["Five of a kind"];
    }

    if ($numberOfJokers > 0) {
        $counts = adjustCountsBasedOnJokers($counts, $numberOfJokers);
    }


    $type = 'High card';

    if (count($counts) == 1) {
        $type = 'Five of a kind';
    } elseif (count($counts) == 2) {
        if (max($counts) == 4) {
            $type = 'Four of a kind';
        } elseif (max($counts) == 3) {
            $type = 'Full house';
        }
    } elseif (count($counts) == 3) {
        if (max($counts) == 3) {
            $type = 'Three of a kind';
        } else {
            $type = 'Two pair';
        }
    } elseif (count($counts) == 4) {
        $type = 'One pair';
    }

    return $types[$type];
}

/**
 * Determines the type of the hand based on the counts of each card.
 *
 * @param array $counts An associative array representing the counts of each card.
 *                      The keys are the card values and the values are the counts.
 * @return string The type of the hand.
 *                The type is represented by a string value according to the following mapping:
 */
function determineHandType(array $counts): string {
    $countOfCounts = count($counts);

    if ($countOfCounts === 1) return 'Five of a kind';
    if ($countOfCounts === 2) {
        if (max($counts) === 4) return 'Four of a kind';
        if (max($counts) === 3) return 'Full house';
    }
    if ($countOfCounts === 3) {
        if (max($counts) === 3) return 'Three of a kind';
        return 'Two pair';
    }
    if ($countOfCounts === 4) return 'One pair';

    return 'High card';
}

/**
 * Adjusts the counts of cards based on the number of jokers and the cards with the maximum frequency.
 *
 * @param array $counts An array representing the counts of each card.
 * @param int $numberOfJokers The number of jokers in the hand.
 * @return array The adjusted counts of cards.
 */
function adjustCountsBasedOnJokers($counts, $numberOfJokers) {
    $maxCountVal = max($counts);
    $maxCountCards = [];

    // Find the cards in the hand with max frequency
    foreach($counts as $card => $count) {
        if($count == $maxCountVal) {
            $maxCountCards[] = $card;
        }
    }

    // Sort the set of cards with max frequency in descending order
    rsort($maxCountCards);
    $newCount = $numberOfJokers + $maxCountVal;
    $counts[$maxCountCards[0]] = $newCount;

    return $counts;
}

/**
 * Orders an array of hands in ascending order based on the rank and card values.
 *
 * @param array $hands An array of hands, where each hand is represented by an associative array
 *                     with a 'hand' key that contains the string representation of the cards in the hand.
 * @return array The ordered array of hands.
 */
function orderHands(array $hands): array
{
    usort($hands, function ($a, $b) {
        $rankA = handRank($a['hand']);
        $rankB = handRank($b['hand']);

        if ($rankA > $rankB) {
            return 1;
        } elseif ($rankA < $rankB) {
            return -1;
        } elseif ($rankA == $rankB) {
            $cardsA = str_split($a['hand']);
            $cardsB = str_split($b['hand']);

            for ($i = 0; $i < 5; $i++) {
                if (cardValue($cardsA[$i]) > cardValue($cardsB[$i])) {
                    return 1;
                } elseif (cardValue($cardsA[$i]) < cardValue($cardsB[$i])) {
                    return -1;
                }
            }
            return 0;
        }

        return 0;
    });

    return $hands;
}

/**
 * Calculates the total winnings in a card game based on the input file path.
 *
 * @param string $filePath The file path pointing to the hand data.
 * @return int The total winnings calculated based on the rank and bid of each hand.
 */
function calculateTotalWinnings(string $filePath): int
{
    $hands = parseHandData($filePath);
    $orderedHands = orderHands($hands);

    $totalWinnings = 0;
    foreach ($orderedHands as $rank => $handData) {
        print $handData['hand'];
        print "\r\n";
        $totalWinnings += ($rank + 1) * $handData['bid'];
    }

    return $totalWinnings;
}

$filePath = 'input.txt';
echo "Total winnings: " . calculateTotalWinnings($filePath) . "\n";
