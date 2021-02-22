<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

use Mike42\Escpos\Printer;
use Mike42\Escpos\CapabilityProfile;
use Mike42\Escpos\PrintConnectors\CupsPrintConnector;

class CTS2000 extends Controller
{
    static protected $lprPrintOptions = "-o cpi=10 -o lpi=8 -o orientation-requested=3";
    static protected $profileName = 'CT-S651';
    static protected $cupsProfile = 'CT_S2000';

    static public $printer = null;
    static protected $cut = false;

    static public function getPrinter()
    {
        if ( self::$printer === null ) {
            $connector = new CupsPrintConnector(self::$cupsProfile );
            $profile = CapabilityProfile::load(self::$profileName);
            self::$printer = new Printer($connector, $profile);
        }
        return self::$printer;
    }

    static public function printFile($file, $cut = true, $usePrinterOptions=true)
    {
        $path = Storage::disk('local')->path($file);

        $opts = $usePrinterOptions ? self::$lprPrintOptions : "";

        $process = new Process("/usr/bin/lpr " . $opts . " " . $path);
        $process->run();
        self::$cut = true;

        return $process->isSuccessful();
    }

    static public function printBarcode($content, $cut = true, $type = Printer::BARCODE_CODE39)
    {
        $printer = self::getPrinter();

        $printer->barcode($content, $type);
        self::$cut = false;

        if ($cut) {
            self::printerCut();
        }
    }

    static public function printQrCode($content, $cut = true, $ecc = Printer::QR_ECLEVEL_L, $size = 3, 
        $model = Printer::QR_MODEL_2)
    {
        $printer = self::getPrinter();

        $printer->qrCode($content, $ecc, $size, $model);
        self::$cut = false;

        if ($cut) {
            self::printerCut();
        }
    }

    static public function printText($string, $cut = true)
    {
        $printer = self::getPrinter();

        $printer->text($string);
        $printer->feed();
        self::$cut = false;

        if ($cut) {
            self::printerCut();
        }
    }

    static public function printerCut()
    {
        if (!self::$cut) {
            self::$cut = true;
            self::getPrinter()->cut();
        }
    }

    static public function terminate()
    {
        if ( self::$printer !== null ) {
            self::$printer->close();
        }
    }
}
