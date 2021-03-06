<?php

namespace App\Http\Middleware;

use BotMan\BotMan\BotMan;
use BotMan\BotMan\Interfaces\Middleware\Heard;
use BotMan\BotMan\Interfaces\Middleware\Sending;
use BotMan\BotMan\Interfaces\Middleware\Captured;
use BotMan\BotMan\Interfaces\Middleware\Matching;
use BotMan\BotMan\Interfaces\Middleware\Received;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;

use App\BotUser;

class ReceivedBotUserAssociator implements Received
{
    /**
     * Handle an incoming message.
     *
     * @param \BotMan\BotMan\Messages\Incoming\IncomingMessage $message
     * @param callable $next
     * @param BotMan $bot
     *
     * @return mixed
     */
    public function received(IncomingMessage $message, $next, BotMan $bot)
    {
        $u = BotUser::withoutGlobalScope('validUser')->where('userid', '=', $message->getSender())->first();

        if ($u == null) {
            $u = new BotUser();
            $u->userid = $message->getSender();
            // Botman has not initialized the incoming message yet and thus is unable to get data from the api
            //$bu = $bot->getUser();
            //$u->firstName = $bu->getFirstName();
            //$u->lastName = $bu->getLastName();
            $u->save();
        }

        $message->addExtras('botuser', $u);

        return $next($message);
    }
}
