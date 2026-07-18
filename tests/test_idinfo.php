<?php
$user = \App\Models\User::first();
\Illuminate\Support\Facades\Auth::login($user);
$controller = new \Chatify\Http\Controllers\MessagesController();
$request = \Illuminate\Http\Request::create('/chatify/idInfo', 'POST', ['id' => 5]);
$response = $controller->idFetchData($request);
echo "RESPONSE:\n";
echo $response->getContent();
echo "\n";
