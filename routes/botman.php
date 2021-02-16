<?php
use App\Http\Controllers\BotManController;
use App\Http\Controllers\BotUserController;

use App\Http\Middleware\MatchPrintingAllowed;

$botman = resolve('botman');

$botman->hears('/start', BotmanController::class.'@start');
$botman->hears('/help', BotmanController::class.'@help');
$botman->hears('/status', BotUserController::class.'@status');
$botman->hears('/license', BotUserController::class.'@register');

$botman->group(['middleware' => new MatchPrintingAllowed()], function ($botman) {
    $botman->hears('([^/].*)', BotmanController::class.'@doPrint');
});

$botman->group(['recipient' => config('printer.telegram_administrator_id')], function ($botman) {
    $botman->hears('/verifyLicense {botuser}', BotUserController::class.'@verifyLicense');
});
