<?php

/**
 * @file
 * This is an ugly code generation script to save time. It parses a PDF file from the IRD website (search for "IR Electronic Filing Payroll Specification Document" or similar.
 * The bank account information is in the appendix, under "Bank account number validation".
 *
 * Copy-paste the tables as plaintext into the $table variable so that the columns are separated by spaces and rows by newlines,
 * without the header row. You may have to clean the data up to make it all appear on one line.
 *
 * @author Neil Bertram <neil@fishy.net.nz>
 * @copyright Copyright(c) 2015 Neil Bertram
 * @link https://github.com/rchouinard/rych-otp
 * @license GPL v3 or later, see LICENSE
 */

// Latest source: http://www.ird.govt.nz/resources/5/0/502c0d02-4a12-493a-8d6d-cf0560071c7d/payroll-spec-2016-v1+2.pdf

// Section 1 - algorithm multiplier map
$multipliers = <<<END
A 0 0 6 3 7 9 0 0 10 5 8 4 2 1 0 0 0 0 11
B 0 0 0 0 0 0 0 0 10 5 8 4 2 1 0 0 0 0 11
*C 3 7 0 0 0 0 9 1 10 5 3 4 2 1 0 0 0 0 11
D 0 0 0 0 0 0 0 7 6 5 4 3 2 1 0 0 0 0 11
E 0 0 0 0 0 0 0 0 0 0 5 4 3 2 0 0 0 1 11
F 0 0 0 0 0 0 0 1 7 3 1 7 3 1 0 0 0 0 10
G 0 0 0 0 0 0 0 1 3 7 1 3 7 1 0 3 7 1 10
*X 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 1
END;

// Section 2 - algorithm selection
$mappings = <<<END
01 0001 - 0999, 1100 - 1199, 1800 - 1899 See note
02 0001 - 0999, 1200 - 1299 See note
03 0001 - 0999, 1300 - 1399, 1500 - 1599, 1700 – 1799, 1900 - 1999 See note
06 0001 - 0999, 1400 - 1499 See note
08 6500 - 6599 D
09 0000 E
11 5000 - 6499, 6600 - 8999 See note
12 3000 - 3299, 3400 – 3499, 3600 - 3699 See note
13 4900 - 4999 See note
14 4700 - 4799 See note
15 3900 - 3999 See note
16 4400 - 4499 See note
17 3300 - 3399 See note
18 3500 - 3599 See note
19 4600 - 4649 See note
20 4100 - 4199 See note
21 4800 - 4899 See note
22 4000 - 4049 See note
23 3700 - 3799 See note
24 4300 - 4349 See note
25 2500 - 2599 F
26 2600 - 2699 G
27 3800 - 3849 See note
28 2100 - 2149 G
29 2150 - 2299 G
30 2900 - 2949 See note
31 2800 - 2849 X
33 6700 - 6799 F
35 2400 - 2499 See note
38 9000 - 9499 See note
END;

// Parse multipliers - this gets pasted into the validate method
$rows = preg_split('/\n/', $multipliers);
foreach ($rows as $line) {
    $bits = preg_split('/ /', $line);
    $alg = ltrim(array_shift($bits), '*'); // chuck out the *, it's not meaningful to us
    echo "    '$alg' => array(";
    foreach ($bits as $bit) {
        echo "$bit, ";
    }
    echo "\010\010),\n";
}

echo "\n\n";

// Parse the bank range selection part, this goes into the getAlgorithm() method
$count = 0;
foreach (preg_split('/\n/', $mappings) as $line) {
    $line = str_replace('–', '-', $line); // replace occasional em dashes with normal ones
    if (preg_match('/^([\d]{2}) ([0-9 -–,]+?) ([A-GXSe not]+)$/', $line, $m)) {
        $op = $count++ == 0 ? 'if' : 'else if';
        echo '    ' . $op . ' ($bank == \'' . $m[1] . '\' && ';
        $ranges = array();
        $bits = explode(', ', $m[2]);
        foreach ($bits as $bit) {
            $range_arr = array_map('trim', explode(' - ', $bit));
            $ranges[] = $range_arr;
        }
        if (count($ranges) == 1) {
            // only one branch or one range of branch numbers
            $range = array_pop($ranges);
            if (!isset($range[1])) {
                echo '$branch == \'' . $range[0] . '\'';
            }
            else {
                echo '($branch >= ' . intval($range[0]) . ' && $branch <= ' . intval($range[1]) . ')';
            }
        }
        else {
            echo '(';
            foreach ($ranges as $range) {
                if (count($range) == 1) {
                    die("\n\nUnexpected range on $line\n" . print_r($range, TRUE));
                }
                echo '($branch >= ' . intval($range[0]) . ' && $branch <= ' . intval($range[1]) . ') || ';
            }
            echo "\010\010\010\010)";
        }
        echo ") {\n        ";
        if ($m[3] == 'See note') {
            echo "if (\$account < 990000) {\n            return 'A';\n        }\n        else {\n            return 'B';\n        }\n";
        }
        else {
            echo "return '" . $m[3] . "';\n";
        }
        echo "    }\n";
    }
    else {
        die("\nUnrecognised row: $line\n");
    }
}
echo "    else {\n        return FALSE;\n    }\n}\n";

