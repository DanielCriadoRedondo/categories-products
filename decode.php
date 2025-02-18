<?php

function base64UrlDecode($input)
{
    $remainder = strlen($input) % 4;
    if ($remainder) {
        $input .= str_repeat('=', 4 - $remainder);
    }
    return base64_decode(strtr($input, '-_', '+/'));
}

function decodeJwt($jwt)
{
    $parts = explode('.', $jwt);
    if (count($parts) !== 3) {
        die("Token JWT inválido.");
    }

    $header = json_decode(base64UrlDecode($parts[0]), true);
    $payload = json_decode(base64UrlDecode($parts[1]), true);

    return [
        'header' => $header,
        'payload' => $payload,
    ];
}

$token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE3Mzk3NDYyNDUsImV4cCI6MTczOTc0OTg0NSwicm9sZXMiOlsiUk9MRV9VU0VSIl0sInVzZXJuYW1lIjoiZGNyaWFkb3JAZ21haWwuY29tIn0.Hy-K1pjNeQxJKkD4cAW2wi3jjGl32azsURG4tGfRO4-impzF2NSjbKcDP1rlcJu8LUzw9rLl_XiAWme03vfvaGwQ9U91K7izgQdwp1XynTSj645XAWceJm6rwTm2JiOvFrNz086y1iHQzzPCoYlNN_G-xMP5gYUI_wkaGrZ81XVIXWxueH__3sNdeSHJXtwgVAKryPJkYtUsL4ADmfAFrALvrUmaFpoPEQgcMeEOKSQNPOKB_MADl3bzW2x66_L24yB1OPMAgAxQoCvzXbmvXycaTLuTq-3QAhWxZDAKxIJFJjj7jwLR576suRWJg0UkeqWBlRXwnBaqfjyZVkuvYcET3AbteqkxI7Ycn1y0hVRsjy1WegE6_2fgTRyMU_-j0BSs2rzbb-7TcnbFeI1w_gZm95AMCH4cW0wz7_ysvjaTbr5IeR22sh8fvfGOqTJTK65QsqyLlvY0dl0XFK45jUsVqqmbMjSTOUN5Qz_1KktmVxCiXq_aPPn-yMissMs8JipBMHweRe5xjhfqHqPHWsl6W5Z9UStaEM1AvXY9jCNRWmDTANwG8gIuM8CWLSpI36HWd2JkRE7Pt8P3bJ3yCaFXdBHLEQz79glw5beZNtM2xJmkqW1OIQuBaZtkBgO0JqalEswETVu8lk2VLYO5wJGap1y3X_ZlDRq7pFOKhbs'; // Reemplázalo por tu token real
$decodedToken = decodeJwt($token);

echo "HEADER:\n";
print_r($decodedToken['header']);

echo "\nPAYLOAD:\n";
print_r($decodedToken['payload']);
