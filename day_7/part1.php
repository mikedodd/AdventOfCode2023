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
    $values = ['2' => 2, '3' => 3, '4' => 4, '5' => 5, '6' => 6, '7' => 7, '8' => 8, '9' => 9, 'T' => 10, 'J' => 11, 'Q' => 12, 'K' => 13, 'A' => 14];
    return $values[$card];
}

/**
 * Determines the rank of a hand in a card game based on the input hand string.
 *
 * @param string $hand The hand string representing the cards in the hand.
 * @return int An array containing the rank of the hand.
 *               The rank is represented by an integer value according to the following mapping:
 */
function handRank(string $hand): int
{
    // Count the occurrences of each card
    $counts = array_count_values(str_split($hand));
    arsort($counts); // Sort by frequency and value

    $types = ['Five of a kind' => 9, 'Four of a kind' => 8, 'Full house' => 7, 'Three of a kind' => 6, 'Two pair' => 5, 'One pair' => 4, 'High card' => 3];
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
    } else {
        $type = 'High card';
    }

    return $types[$type];
}

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
