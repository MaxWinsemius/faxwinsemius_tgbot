<?php

namespace App\Conversations;

use App\Message;
use App\BotUser;

use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Conversations\Conversation;

class AllstatsConversation extends Conversation
{
    /**
     * Start the conversation.
     *
     * @return mixed
     */
    public function run()
    {
        $this->askType();
    }

    public function askType()
    {
        $question = Question::create("What do you want to see?")
            ->fallback("I just wanted to ask what you wanted to see...")
            ->callbackId('ask_type')
            ->addButtons([
                Button::create('Totals')->value('total'),
                Button::create('People specifics')->value('people'),
            ]);

        return $this->ask($question, function (Answer $answer) {
            if ($answer->isInteractiveMessageReply()) {
                switch ($answer->getValue()) {
                case "total":
                    $amtLicenses = BotUser::where('printAccess', '=', 'true')->count();
                    $msg = "There are " . BotUser::count(). " people that are trying to chat with your fax, but only " 
                        . $amtLicenses . " have a print license. With scheme (Total) / (avg Per License) the following stats are available:\n";

                    $msg .= "Amount of messages: " . Message::count() . " / " . Message::count() / $amtLicenses . " \n";
                    $msg .= "Amount of bytes: " . Message::sum('stat') . " / " . Message::sum('stat') / $amtLicenses . " \n";
                    $msg .= "Amount of characters: " . Message::whereType(Message::TYPE_TEXT)->sum('stat') . " / " . Message::whereType(Message::TYPE_TEXT)->sum('stat') / $amtLicenses . " \n";
                    $msg .= "Amount of image bytes: " . Message::whereType(Message::TYPE_IMAGE)->sum('stat') . " / " . Message::whereType(Message::TYPE_IMAGE)->sum('stat') / $amtLicenses . " \n";
                    $this->say($msg);
                    break;
                case "people":
                    break;
                default:
                    $this->say("I do not understand what you mean");
                }
            }
        });
    }
}
