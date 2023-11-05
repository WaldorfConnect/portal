<?php

namespace App\Helpers;

function getCronLog(): string
{
    return readfile(getenv('cron.log'));
}