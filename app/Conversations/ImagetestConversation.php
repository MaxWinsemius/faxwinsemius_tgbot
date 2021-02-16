<?php

namespace App\Conversations;

use BotMan\BotMan\Messages\Conversations\Conversation;
use Illuminate\Support\Facades\Log;

class ImagetestConversation extends Conversation
{
    /**
     * Start the conversation.
     *
     * @return mixed
     */
    public function run()
    {
        $this->askPhoto();
    }

    public function askPhoto()
    {
        return $this->askForImages('Image ploxoxoxox', function($images) {
            foreach ($images as $image) {
                $url = $image->getUrl();
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_NOBODY, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HEADER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

                $data = curl_exec($ch);
                curl_close($ch);

                Log::debug(json_encode($data));
            }

            $this->say('Thanks for your offering. It will be used wisely');
        });
    }
}
