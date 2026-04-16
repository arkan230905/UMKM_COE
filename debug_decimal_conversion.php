<?php

function smartNumberConversion($value)
{
    // Remove any spaces
    $value = trim($value);
    
    // If empty, return as is
    if (empty($value)) {
        return $value;
    }
    
    // Count dots and commas to determine format
    $dotCount = substr_count($value, '.');
    $commaCount = substr_count($value, ',');
    
    echo "Input: '$value'\n";
    echo "Dot count: $dotCount, Comma count: $commaCount\n";
    
    // If no dots or commas, it's already a clean number
    if ($dotCount === 0 && $commaCount === 0) {
        echo "No separators, returning as is: $value\n\n";
        return $value;
    }
    
    // Find positions of last dot and comma
    $lastDotPos = strrpos($value, '.');
    $lastCommaPos = strrpos($value, ',');
    
    // Case 1: Only commas (Indonesian decimal: 1,5 or 1000,50)
    if ($commaCount > 0 && $dotCount === 0) {
        echo "Only commas, converting to decimal: " . str_replace(',', '.', $value) . "\n\n";
        return str_replace(',', '.', $value);
    }
    
    // Case 2: Only dots
    if ($dotCount > 0 && $commaCount === 0) {
        echo "Only dots case\n";
        // If multiple dots, treat all but last as thousand separators
        if ($dotCount > 1) {
            $parts = explode('.', $value);
            $lastPart = array_pop($parts);
            // If last part has 3 digits, it's likely a thousand separator (e.g., 1.000.000)
            if (strlen($lastPart) === 3 && ctype_digit($lastPart)) {
                echo "Multiple dots, treating as thousand separators: " . implode('', array_merge($parts, [$lastPart])) . "\n\n";
                return implode('', array_merge($parts, [$lastPart]));
            } else {
                // Last part is decimal (e.g., 1.000.50)
                echo "Multiple dots, last part is decimal: " . implode('', $parts) . '.' . $lastPart . "\n\n";
                return implode('', $parts) . '.' . $lastPart;
            }
        } else {
            // Single dot - check if it's thousand separator or decimal
            $parts = explode('.', $value);
            if (count($parts) === 2) {
                $beforeDot = $parts[0];
                $afterDot = $parts[1];
                
                echo "Single dot analysis:\n";
                echo "Before dot: '$beforeDot' (length: " . strlen($beforeDot) . ")\n";
                echo "After dot: '$afterDot' (length: " . strlen($afterDot) . ")\n";
                echo "Is after dot 3 digits? " . (strlen($afterDot) === 3 ? 'yes' : 'no') . "\n";
                echo "Is after digit all digits? " . (ctype_digit($afterDot) ? 'yes' : 'no') . "\n";
                echo "Is before dot 1-3 digits? " . ((strlen($beforeDot) >= 1 && strlen($beforeDot) <= 3 && ctype_digit($beforeDot)) ? 'yes' : 'no') . "\n";
                
                // If after dot has exactly 3 digits and all are digits, and before dot is 1-3 digits
                // it's likely a thousand separator (e.g., 1.000, 15.000)
                if (strlen($afterDot) === 3 && ctype_digit($afterDot) && 
                    strlen($beforeDot) >= 1 && strlen($beforeDot) <= 3 && ctype_digit($beforeDot)) {
                    echo "Treating as thousand separator: " . $beforeDot . $afterDot . "\n\n";
                    return $beforeDot . $afterDot;
                } else {
                    // Otherwise it's a decimal separator (e.g., 2.5, 50.75)
                    echo "Treating as decimal separator: $value\n\n";
                    return $value;
                }
            } else {
                echo "More than 2 parts, returning as is: $value\n\n";
                return $value;
            }
        }
    }
    
    return $value;
}

// Test cases
$testCases = ['1.5', '1,5', '1.500', '1.000', '2.5', '10.5', '100.5'];

foreach ($testCases as $test) {
    $result = smartNumberConversion($test);
    echo "Result: $result\n";
    echo "================\n";
}
