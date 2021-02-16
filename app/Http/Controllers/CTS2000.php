<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use Symfony\Component\Process\Process;

class CTS2000 extends Printer
{
    static public function printFile($file)
    {
        Storage::disk('local')->append('max_log.log', 'hello');

        $link = Storage::disk('local')->url($file);

        $process = new Process('cat ' . $link . " | lpr");
        $process->run();

        return $process->isSuccessful();
    }

    static public function printText(string $text)
    {
        $process = new Process('echo "' . addslashes($text) . '" | lpr');
        $process->run();

        return $process->isSuccessful();
    }
}
