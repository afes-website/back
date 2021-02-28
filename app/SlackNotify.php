<?php
namespace App;

use App\Models\Article;
use App\Models\Draft;
use App\Models\Exhibition;
use App\Models\Revision;
use App\Models\Image;

class SlackNotify {
    public static function send($payload) {
        if (!env('NOTIFY_SLACK_WEBHOOK_URL')) return;
        if (!is_array($payload)) {
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

    public static function notify_article(Article $article, string $action, string $user_name) {
        self::send([
            "text" => "{$user_name} has {$action} article {$article->id}",
            "attachments" => [
                [
                    "text"=>
                        "title: {$article->title}\n".
                        "category: {$article->category}\n".
                        "<".env('FRONT_URL')."/blog/admin/paths/{$article->id}|manage>\n".
                        "<".env('FRONT_URL')."/blog/{$article->category}/{$article->id}|show>\n"
                ],
            ]
        ]);
    }

    public static function notify_revision(Revision $revision, string $action, string $user_name) {
        self::send([
            "text" => "{$user_name} has {$action} revision {$revision->id}",
            "attachments" => [
                [
                    "text"=>
                        "title: {$revision->title}\n".
                        "<".env('FRONT_URL')."/blog/admin/revisions/{$revision->id}|preview>\n".
                        "<".env('FRONT_URL')."/blog/admin/paths/{$revision->article_id}|manage>"
                ],
            ]
        ]);
    }

    public static function notify_image(Image $image, string $action, string $user_name) {
        self::send([
            "text" => "{$user_name} has {$action} image {$image->id}",
            "attachments" => [
                [
                    "fields" => [[
                        "value" =>
                            "id: `{$image->id}`\n".
                            "<".env('APP_URL')."/images/{$image->id}|open>"
                    ],
                    ],
                    "image_url" => env('APP_URL')."/images/{$image->id}",
                    "mrkdwn_in" => [ "fields" ]
                ],
            ]
        ]);
    }

    public static function notify_exhibition(Exhibition $exhibition, string $action, string $user_name) {
        self::send([
            "text" => "{$user_name} has {$action} exhibition {$exhibition->id}",
            "attachments" => [
                [
                    "text"=>
                        "name: {$exhibition->name}\n".
                        "<".env('FRONT_URL')."/admin/exh/{$exhibition->id}|manage>\n".
                        "<".env('FRONT_URL')."/exh/{$exhibition->id}|show>\n"
                ],
            ]
        ]);
    }

    public static function notify_draft(Draft $draft, string $action, string $user_name) {
        self::send([
            "text" => "{$user_name} has {$action} draft {$draft->id}",
            "attachments" => [
                [
                    "text"=>
                        "exh_name: {$draft->exhibition->name}\n".
                        "<".env('FRONT_URL')."/admin/exh/draft/{$draft->id}|preview>\n".
                        "<".env('FRONT_URL')."/admin/exh/draft|manage>\n"
                ],
            ]
        ]);
    }
}
