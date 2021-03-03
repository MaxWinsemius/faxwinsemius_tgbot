<?php
use App\Http\Controllers\BotManController;
use App\Http\Controllers\BotUserController;

use App\Http\Middleware\MatchPrintingAllowed;

$botman = resolve('botman');

$botman->hears('/start', BotManController::class.'@start');
$botman->hears('/help', BotManController::class.'@help');
$botman->hears('/status', BotUserController::class.'@status');
$botman->hears('/stats', BotUserController::class.'@status');
$botman->hears('/license', BotUserController::class.'@register');

$botman->group(['middleware' => new MatchPrintingAllowed()], function ($botman) {
    $botman->hears('([^/].*)', BotManController::class.'@doPrint');
});

$botman->group(['recipient' => config('printer.telegram_administrator_id')], function ($botman) {
    $botman->hears('/verifyLicense {botuser}', BotUserController::class.'@verifyLicense');
    $botman->hears('/startTest', BotManController::class.'@doAdmin');
});
