<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Symfony\Component\Process\Process;

class CTS2000 extends Printer
{
    static public function printText(string $text)
    {
        $process = new Process('echo "' . addslashes($text) . '" | lpr');
        $process->run();

        return $process->isSuccessful();
    }
}
