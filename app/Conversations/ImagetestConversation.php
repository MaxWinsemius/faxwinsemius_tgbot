<?php

namespace App\Conversations;

use BotMan\BotMan\Messages\Conversations\Conversation;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\CTS2000;

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

                Log::debug(print_r($data, $return=true));
                Log::debug("The title is: " . $image->getTitle());
                Log::debug("Payload: ");
                Log::debug(print_r($data, $return=true));

                if (preg_match('/Content-Length: (\d+)/', $data, $matches)) {
                    $contentLength = (int)$matches[1];
                    Log::debug("File is " . $contentLength . " bytes");

                    if ($contentLength < 500000) {
                        $this->say("Filesize is printable, let's try");

                        $ch = curl_init($url);
                        curl_setopt($ch, CURLOPT_HEADER, false);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                        if (!$result = curl_exec($ch)) {
                            $this->say('Curling went wrong :(');
                        }
                        curl_close($ch);

                        CTS2000::printText($result);
                        $this->say('Printing an image...');
                    }
                }
            }

            $this->say('Thanks for your offering. It will be used wisely');
        });
    }
}
