<?php

namespace App\Controllers;

class IndexController extends BaseController
{
    public function index()
    {
        return view('welcome_message');
    }
}
