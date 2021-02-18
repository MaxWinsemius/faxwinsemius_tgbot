<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\Http\Controllers\CTS2000;
use Illuminate\Support\Facades\Storage;

class Message extends Model
{
    const TYPE_TEXT = 0;
    const TYPE_IMAGE = 1;
    const TYPES = [self::TYPE_TEXT, self::TYPE_IMAGE];

    protected $fillable = ['file', 'type', 'fileAvailable', 'stat'];

    public function bot_user()
    {
        return $this->belongsTo(BotUser::class);
    }

    /**
     * Sets the message with a data type
     *
     * @return false if the message is already set
     */
    public function setDataAndType($data, $type)
    {
        if ( $this->type !== null ) {
            return false;
        }

        $this->type = $type;
        $this->file = self::generateFileName($data);
        Storage::disk('local')->put($this->file, $data);
        $this->fileAvailable = true;
        $this->save();

        $this->calculateStat();

        return true;
    }

    /**
     * Sets the message as a text message
     *
     * @return False if the message is already set
     */
    public function setText(string $string)
    {
        return $this->setDataAndType($string, self::TYPE_TEXT);
    }

    /**
     * Sets the message as an image message
     *
     * @return False if the message is already set
     */
    public function setImage($image)
    {
        return $this->setDataAndType($image, self::TYPE_IMAGE);
    }

    /**
     * Either put this in a queue, or just print it directly
     */
    public function dispatch()
    {
        $this->print();
    }

    /**
     * Tries to print the file
     *
     * @return false if printing fails (printer is unavailable) and true on success.
     */
    public function print()
    {
        switch ($this->type) {
        case self::TYPE_TEXT:
            CTS2000::printText(Storage::get($this->file));
            //CTS2000::printFile($this->file);
            break;
        case self::TYPE_IMAGE:
            CTS2000::printFile($this->file);
            break;
        }

        // TODO: base this value off if cups has printed the file
        $this->printed = true;
        $this->save();

        return $this->printed;
    }

    /**
     * Remove the file that is associated to this message
     */
    public function deleteFile()
    {
        // First off, check if the file actually exists
        if (!$this->fileAvailable) {
            // File is already deleted
            return;
        }

        // Before removing file make sure the stats are saved
        if (!$this->stat) {
            $this->calculateStat;
        }

        Storage::delete($this->file);
        $this->fileAvailable = false;
        $this->save();
    }

    /**
     * reserved for file updating and removing
     */
    public function postDispatch()
    {
        return true;
    }

    /**
     * calculates the stat based on the file, if the file is still available
     */
    public function calculateStat()
    {
        if (!$this->fileAvailable) {
            return;
        }

        $this->stat = Storage::size($this->file);

        $this->save();
    }

    public static function generateFileName($data, $hash='sha224')
    {
        return '/messages/' . hash($hash, $data . now()) . '.data';
    }
}
