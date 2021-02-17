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

    public function getStat($type)
    {
        $count = 0;
        $messages;

        if ($type == null) {
            $messages = $this->messages;
        } else {
            $messages = $this->messages()->whereType($type)->get();
        }

        foreach ( $messages as $message ) {
            if ($message->stat == 0) {
                $message->calculateStat();
            }

            $count += $message->stat;
        }

        return $count;
    }

    public function getStatCharacterCount()
    {
        return $this->getStat(Message::TYPE_TEXT);
    }

    public function getStatByteCount()
    {
        return $this->getStat(Message::TYPE_IMAGE);
    }
}
