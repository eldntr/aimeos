<?php
$user = App\Models\User::first();
Auth::login($user);

$controller = new \Chatify\Http\Controllers\MessagesController();

$request = new \Illuminate\Http\Request();
$request->merge(['id' => 3]);
$response = $controller->idFetchData($request);
echo "IDINFO: " . json_encode($response->getData()) . "\n";

$request2 = new \Illuminate\Http\Request();
$request2->merge(['id' => 3, 'page' => 1]);
$response2 = $controller->fetch($request2);
echo "FETCH: " . substr(json_encode($response2->getData()), 0, 500) . "\n";
