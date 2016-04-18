<?php
// Routes

$app->get('/', App\Action\HomeAction::class)->setName('homepage');
$app->get('/kpi/monthly', App\Action\Kpi\MonthlyAction::class)->setName('kpi.monthly');
$app->get('/kpi/daily', App\Action\Kpi\DailyAction::class)->setName('kpi.daily');
$app->get('/kpi/shift', App\Action\Kpi\ShiftAction::class)->setName('kpi.shift');
