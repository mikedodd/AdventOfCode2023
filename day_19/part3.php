<?php

$start_time = microtime(true);

$_fp = fopen( $argv[1] ?? "input.txt", "r");

$part1 = $part2 = 0;

$W = [];
while (!feof($_fp) && $line = trim(fgets($_fp)))
{
    [$id, $rules] = preg_split("/[{}]/", $line, -1, PREG_SPLIT_NO_EMPTY);
    $rules = explode(",", $rules);
    $rules = array_map(fn($a) => preg_split("/(?:([<>])|[:])/", $a, 3, PREG_SPLIT_DELIM_CAPTURE), $rules);
    $W[$id] = $rules;
}

$P = [];
while (!feof($_fp) && $line = trim(fgets($_fp)))
{
    $part = explode(",", trim($line, "{}"));
    $part = array_map(fn($a) => explode("=", $a)[1], $part);
    $part = array_combine(['x', 'm', 'a', 's'], $part);
    $P[] = $part;

    $id = 'in';
    while ($id !== 'A' && $id != 'R') foreach ($W[$id] as $rule)
    {
        $pass = true;
        if (count($rule) == 4)
        {
            [$rating, $op, $num, $dest] = $rule;
            if ($op == '>')
                $pass = $part[$rating] > $num;
            else
                $pass = $part[$rating] < $num;
        }
        else $dest = $rule[0];

        if ($pass)
        {
            if ($dest == 'A') $part1 += array_sum($part);
            // next workflow...
            $id = $dest; break;
        }
    }
}
fclose($_fp);

function f($op, $num, $start, $end)
{
    switch ($op)
    {
        case '>': $start = max($start, $num + 1); break;
        case '<': $end = min($end, $num - 1); break;
        case '>=': $start = max($start, $num); break;
        case '<=': $end = min($end, $num); break;
    }
    return [$start, $end];
}

function split($rating, $op, $num, $x1, $x2, $m1, $m2, $a1, $a2, $s1, $s2)
{
    switch ($rating)
    {
        case 'x': [$x1, $x2] = f($op, $num, $x1, $x2); break;
        case 'm': [$m1, $m2] = f($op, $num, $m1, $m2); break;
        case 'a': [$a1, $a2] = f($op, $num, $a1, $a2); break;
        case 's': [$s1, $s2] = f($op, $num, $s1, $s2); break;
    }
    return [$x1, $x2, $m1, $m2, $a1, $a2, $s1, $s2];
}

$Q = [['in', 1, 4000, 1, 4000, 1, 4000, 1, 4000]];
while (count($Q))
{
    [$id, $x1, $x2, $m1, $m2, $a1, $a2, $s1, $s2] = array_pop($Q);

    if ($id == 'R' || $x1 > $x2 || $m1 > $m2 || $a1 > $a2 || $s1 > $s2) continue;

    if ($id == 'A')
    {
        $part2 += ($x2-$x1+1) * ($m2-$m1+1) * ($a2-$a1+1) * ($s2-$s1+1);
        continue;
    }

    foreach ($W[$id] as $rule)
    {
        if (count($rule) == 1)
        {
            $Q[] = [$rule[0], $x1, $x2, $m1, $m2, $a1, $a2, $s1, $s2];
            break;
        }
        [$rating, $op, $num, $dest] = $rule;
        $Q[] = [$dest, ...split($rating, $op, $num, $x1, $x2, $m1, $m2, $a1, $a2, $s1, $s2)];
        $new = split($rating, ($op == '>' ? '<=' : '>='), $num, $x1, $x2, $m1, $m2, $a1, $a2, $s1, $s2);
        [$x1, $x2, $m1, $m2, $a1, $a2, $s1, $s2] = $new;
    }
}

echo "part 1: {$part1}\n";
echo "part 2: {$part2}\n";

