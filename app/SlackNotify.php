<?php
namespace App;
class SlackNotify {
    public static function send($payload) {
        if (env('NOTIFY_SLACK_WEBHOOK_URL') === "") return;
        if(!is_array($payload)) {
            $payload = ["text" => $payload];
        }
        $options = [
            'http' => [
              'method' => 'POST',
              'header' => 'Content-Type: application/json',
              'content' => json_encode($payload),
            ]
        ];
        $response = file_get_contents(env('NOTIFY_SLACK_WEBHOOK_URL'), false, stream_context_create($options));
        return $response === 'ok';
    }
}
