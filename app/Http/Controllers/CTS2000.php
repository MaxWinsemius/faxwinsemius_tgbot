<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use Symfony\Component\Process\Process;

class CTS2000 extends Printer
{
    static public function printFile($file)
    {
        $path = Storage::disk('local')->path($file);

        $process = new Process("/usr/bin/lpr " . $path);
        $process->run();

        Storage::disk('local')->delete($file);

        return $process->isSuccessful();
    }

    static public function printText(string $string)
    {
        $file = '/messages/' . hash('sha224', $string . now()) . '.data';
        Storage::disk('local')->put($file, $string);

        return CTS2000::printFile($file);
    }
}
