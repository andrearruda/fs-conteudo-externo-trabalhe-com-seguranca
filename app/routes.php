<?php
// Routes

$app->get('/[{amount}]', App\Action\WorkplaceSafety::class);
