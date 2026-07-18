<?php
$user = \App\Models\User::first();
\Illuminate\Support\Facades\Auth::login($user);
$query = \Chatify\Facades\ChatifyMessenger::fetchMessagesQuery(5);
echo $query->toSql();
echo "\nBindings: ";
print_r($query->getBindings());
