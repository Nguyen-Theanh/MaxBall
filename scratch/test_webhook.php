<?php
$url = 'http://127.0.0.1:8000/webhook/sepay';
$data = ['content' => '7YO6IOLHBM', 'transferAmount' => 230000];
$options = [
    'http' => [
        'header'  => "Content-type: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($data)
    ]
];
$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);
if ($result === FALSE) { echo "Error"; }
echo $result;
