<?php

namespace App\Http\Controllers;

use BotMan\BotMan\BotMan;
use BotMan\Drivers\Telegram\TelegramDriver;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\BotUser;

class BotUserController extends Controller
{
    public function handle()
    {
        $botman = app('botman');

        $botman->middleware->received(new ReceivedBotUserAssociator());
        $botman->middleware->sending(new SendingMarkdownParser());

        $botman->listen();
    }

    public function status(BotMan $bot)
    {
        $msg = "Some information about you";
        $u = BotUser::findUserById($bot);
        $txtdat = $u->getStatCharacterCount();
        $imgdat = $u->getStatByteCount();

        $msg .= "\nYou are " . ($u->printAccess ? "" : "not ") . 'allowed to print.';
        $msg .= "\nYou have printed " . $txtdat . " characters, and " 
            . $imgdat . " bytes of image data, which comes down to a total of " . ($txtdat + $imgdat) . " bytes.";

        //$msg = $msg . "\nUserID: " . $bot->getUser()->getId();

        $bot->reply($msg);
    }

    public function register(BotMan $bot)
    {
        $u = BotUser::withoutGlobalScope('validUser')->ofBotManCall($bot)->first();

        if ( $u->printAccess ) {
            $bot->reply("You're already received a license! Don't waste your time on official matters!");
            return;
        }

        $bu = $bot->getUser();

        $u->firstName = $bu->getFirstName();
        $u->lastName = $bu->getLastName();
        $u->lastPrintRequest = Carbon::now();
        $u->save();

        $bot->say($u->firstName . " " . $u->lastName . " wants to monkey around on your printer.", 
            "" . config('printer.telegram_administrator_id'), TelegramDriver::class    
        );

        $bot->say("/verifyLicense " . $u->userid, 
            "" . config('printer.telegram_administrator_id'), TelegramDriver::class    
        );

        $bot->reply("Printing license request has been sent. Officials will look into this serious request.");
    }

    public function verifyLicense(BotMan $bot, $botuser)
    {
        $u = BotUser::whereUserid($botuser)->first();

        if ($u == null) {
            $bot->reply('I could not find that user, sir');
            return;
        }

        $u->printAccess = true;
        $u->save();

        $bot->reply('Print access granted');

        $bot->say("Hey " . $u->firstName . "! Your printing licence has been approved. Use it wisely!", 
            $u->userid, TelegramDriver::class    
        );
    }
}
