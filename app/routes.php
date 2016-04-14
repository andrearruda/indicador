<?php
// Routes

$app->get('/', App\Action\HomeAction::class)->setName('homepage');
$app->get('/kpi/monthly', App\Action\Kpi\MonthlyAction::class)->setName('kpi.monthly');
