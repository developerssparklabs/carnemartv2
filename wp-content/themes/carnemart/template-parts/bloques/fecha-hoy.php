<?php
$date = new DateTime(); // Fecha actual					
$formatter = new IntlDateFormatter(
    'es_MX',
    IntlDateFormatter::LONG,
    IntlDateFormatter::NONE,
    'America/Mexico_City',
    IntlDateFormatter::GREGORIAN,
    'EEEE, d\'.\'MM\'.\'yyyy'
);
echo $formatter->format($date);
