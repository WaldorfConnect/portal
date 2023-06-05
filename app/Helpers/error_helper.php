<?php

namespace App\Helpers;

use CodeIgniter\HTTP\RedirectResponse;
use Throwable;

function handleException(Throwable $t): RedirectResponse
{
    return redirect('error')->with('error', $t->getMessage());
}