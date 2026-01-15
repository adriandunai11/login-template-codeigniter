<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        echo logged('full_name');
        return view('welcome_message');
    }
}
