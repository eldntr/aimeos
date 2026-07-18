<?php
$user = \App\Models\User::find(5);
\Illuminate\Support\Facades\Auth::login($user);
$controller = new \Chatify\Http\Controllers\MessagesController();
$request = \Illuminate\Http\Request::create('/chatify/fetchMessages', 'POST', ['id' => 5, 'page' => 1]);
$response = $controller->fetch($request);
echo "\n---RESPONSE---\n";
echo $response->getContent();
echo "\n";
