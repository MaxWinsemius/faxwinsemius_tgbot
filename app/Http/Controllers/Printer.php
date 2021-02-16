<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Printer extends Controller
{
    abstract static public function printText(string $text);
}
