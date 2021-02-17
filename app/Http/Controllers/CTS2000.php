<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use Symfony\Component\Process\Process;

class CTS2000 extends Controller
{
    static protected $lprPrintOptions = "-o cpi=10 -o lpi=8 -o orientation-requested=3";

    static public function printFile($file, $usePrinterOptions=true)
    {
        $path = Storage::disk('local')->path($file);

        $opts = $usePrinterOptions ? self::$lprPrintOptions : "";

        $process = new Process("/usr/bin/lpr " . $opts . " " . $path);
        $process->run();

        return $process->isSuccessful();
    }
}
