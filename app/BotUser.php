<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use BotMan\BotMan\BotMan;

class BotUser extends Model
{
    static public function findUserById(BotMan $bot)
    {
        return static::whereUserid($bot->getUser()->getId())->first();
    }

}
