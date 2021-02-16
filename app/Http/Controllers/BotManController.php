<?php

namespace App\Http\Controllers;

use BotMan\BotMan\BotMan;
use BotMan\Drivers\Telegram\TelegramDriver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use App\BotUser;
use App\Conversations\ImagetestConversation;
use App\Http\Controllers\CTS2000;
use App\Http\Middleware\ReceivedBotUserAssociator;
use App\Http\Middleware\SendingMarkdownParser;

class BotManController extends Controller
{
    /**
     * Place your BotMan logic here.
     */
    public function handle()
    {
        $botman = app('botman');

        $botman->middleware->received(new ReceivedBotUserAssociator());
        $botman->middleware->sending(new SendingMarkdownParser());

        $botman->listen();
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function tinker()
    {
        return view('tinker');
    }

    /**
     * Loaded through routes/botman.php
     * @param  BotMan $bot
     */
//    public function startConversation(BotMan $bot)
//    {
//        $bot->startConversation(new ExampleConversation());
//    }

    public function start(BotMan $bot)
    {
        $str = "
Welcome to the FaxWinsemius bot! Here you will be able to send your best messages to the CT-S2000 Receipt printer! To start off, first request a printing /license. After that you can print any message that does not start with a '/'. Send '/help' if you want help. Have fun!
";
        $bot->reply($str);
    }

    protected function printImages($images)
    {
        foreach ($images as $image) {
            $url = $image->getUrl();

            // Get length
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

            $data = curl_exec($ch);
            curl_close($ch);

            if (preg_match('/Content-Length: (\d+)/', $data, $matches)) {
                $contentLength = (int)$matches[1];

                // Check if length is not too big
                if ($contentLength < 500000) {

                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_HEADER, false);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                    if (!$result = curl_exec($ch)) {
                        $this->say("Officers had a really bad time on working the image. It's unworkable.");
                    }
                    curl_close($ch);

                    CTS2000::printText($result);
                    $this->say('Officers are working on your image');
                }
            }
        }
    }

    public function doAdmin(BotMan $bot)
    {
        $bot->startConversation(new ImagetestConversation());
    }

    public function doPrint(BotMan $bot, string $string)
    {
        $payload = $bot->getMessage()->getPayload();

        $bot->receivesImages(function($bot, $images) {
            if (count($images) > 0) {
                $this->printImages($images);
                return;
            }
        });

        if ($bot->getName() == 'TelegramPhoto') {
            $bot->reply("YOU HAVE SENT AN IMAGE. THE OFFICERS WILL COME AND BURN YOU. WITH THERMAL PAPER. Unless... it is too much effort");
            return;
        }

        $bot->receivesVideos(function($bot, $videos) {
            if (count($videos) > 0) {
                $bot->reply("You fool! What do you even imagine what would happen?");
                return;
            }
        });

        $message = "";

        if ($bot->getName() == 'Telegram') {
            //CTS2000::printText($payload['chat']['text']);
            $message = $payload['text'];
        } else {
            $message = $string;
        }

        $file = "messages/" . hash('sha224', $message . now()) . ".data";
        Storage::disk('local')->put($file, $message);
        CTS2000::printFile($file);

        $bot->reply("Your message has been sent.");

        // Notify administrator

        $u = BotUser::findUserById($bot);

        $bot->say($u->firstName . " " . $u->lastName . " has sent you a fax", 
            "" . config('printer.telegram_administrator_id'), TelegramDriver::class    
        );
    }

    public function help(BotMan $bot)
    {
        $str = "
https://www.youtube.com/watch?v=FP9y7F_rzzo

/help Show this help message.
/status Show some status information about yourself.
/license Request a license from the officials.

Once you get a printing license, you can print any message that does not start with a '/'

The officials do not like pictures. Do not try to send pictures. Pictures are forbidden.
            ";

        $bot->reply($str);
    }
}
