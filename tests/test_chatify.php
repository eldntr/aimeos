<?php
$user = App\Models\User::first();
Auth::login($user);
$request = Illuminate\Http\Request::create('/chatify/idInfo', 'POST', ['id' => $user->id]);
$kernel = app(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request);
echo "STATUS: " . $response->getStatusCode() . "\n";
echo "CONTENT: " . $response->getContent() . "\n";

$request2 = Illuminate\Http\Request::create('/chatify/fetchMessages', 'POST', ['id' => $user->id, 'page' => 1]);
$response2 = $kernel->handle($request2);
echo "STATUS 2: " . $response2->getStatusCode() . "\n";
echo "CONTENT 2: " . substr($response2->getContent(), 0, 200) . "\n";
