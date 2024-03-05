<?php

namespace App\Helpers;

function getCronLog(string $service): string
{
    return readfile(getenv("cron.log_{$service}"));
}