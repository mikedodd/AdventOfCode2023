<?php

/**
 * Parses the input file and returns an array containing instructions and network.
 *
 * @param string $filePath The file path of the input file.
 * @return array An array containing 'instructions' and 'network' keys.
 */
function parseInput(string $filePath): array {
    $lines = file($filePath, FILE_IGNORE_NEW_LINES);
    $instructions = array_shift($lines);
    $network = [];

    foreach ($lines as $line) {
        if($line) {
            list($node, $connections) = explode(' = ', $line);
            $network[$node] = explode(', ', trim($connections, '()'));
        }
    }

    return ['instructions' => $instructions, 'network' => $network];
}

/**
 * Returns an array containing the starting nodes in the given network.
 *
 * @param array $network The network to search for starting nodes.
 * @return array An array containing the starting nodes found in the network.
 */
function findStartingNodes(array $network): array {
    return array_filter(array_keys($network), function($node) {
        return str_ends_with($node, 'A');
    });
}


/**
 * Calculates the length of a cycle in a network based on a given set of instructions.
 *
 * @param array $network The network represented as an array where keys are nodes and values are arrays containing the neighboring nodes in clockwise direction.
 * @param string $instructions The instructions for traversing the network. Each character in the string indicates whether to turn right ('R') or left ('L') at each step.
 * @param string $startNode The starting node in the network.
 *
 * @return int The length of the cycle in the network.
 */
function findCycleLength(array $network, string $instructions, string $startNode): int {
    $currentNode = $startNode;
    $steps = 0;

    do {
        $direction = $instructions[$steps % strlen($instructions)] === 'R' ? 1 : 0;
        $currentNode = $network[$currentNode][$direction];
        $steps++;
    } while (!str_ends_with($currentNode, 'Z'));

    return $steps;
}

/**
 * Calculates the least common multiple (LCM) of two integers.
 *
 * @param int $a The first integer.
 * @param int $b The second integer.
 * @return int The least common multiple of $a and $b.
 */
function lcm(int $a, int $b): int {
    return ($a * $b) / gcd($a, $b);
}

/**
 * Calculates the greatest common divisor (GCD) of two integers.
 *
 * @param int $a The first integer.
 * @param int $b The second integer.
 * @return int The greatest common divisor of $a and $b.
 */
function gcd(int $a, int $b): int {
    if ($b == 0) {
        return $a;
    }
    return gcd($b, $a % $b);
}

/**
 * Calculates the Least Common Multiple (LCM) of an array of positive integers.
 *
 * @param array $cycleLengths An array of positive integers for which the LCM is to be calculated.
 *                            Must provide at least two integers.
 * @return int The calculated LCM of the given positive integers.
 *         Returns false if any of the parameters is not a positive integer or if no parameters are provided.
 *         Returns 0 if any of the parameters is 0.
 */
function calculateLCM(array $cycleLengths): int {
    $lcm = 1;
    foreach ($cycleLengths as $length) {
        $lcm = lcm($lcm, $length);
    }
    return $lcm;
}

/**
 * Navigates a network based on a set of instructions and returns the calculated result.
 *
 * @param array $network An array representing the network.
 * @param string $instructions The instructions to navigate the network.
 * @return int The calculated result of navigating the network.
 */
function navigateNetwork(array $network, string $instructions): int {
    $startingNodes = findStartingNodes($network);
    $cycleLengths = [];

    foreach ($startingNodes as $startNode) {
        $cycleLengths[] = findCycleLength($network, $instructions, $startNode);
    }

    return calculateLCM($cycleLengths);
}

$filePath = 'input.txt';
$data = parseInput($filePath);

echo "Steps to reach all Z nodes with LCM: " . navigateNetwork($data['network'], $data['instructions']) . "\n";