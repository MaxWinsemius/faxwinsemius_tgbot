<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use BotMan\BotMan\BotMan;
use BotMan\Drivers\Telegram\TelegramDriver;

class BotUser extends Model
{
    /**
     * The booting method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        if (app()->environment() != 'local') {
            static::addGlobalScope('validUser', function(Builder $builder) {
                $builder->whereNotNull('firstName')->orWhereNotNull('lastName');
            });
        }
    }

    static public function findUserById(BotMan $bot)
    {
        return static::whereUserid($bot->getUser()->getId())->first();
    }

    public function sendMessage($string)
    {
        $bot = app('botman');

        $bot->say($string, $this->userid, TelegramDriver::class);
        // TODO: test this in tg, webdriver does not seem to support this feature
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
