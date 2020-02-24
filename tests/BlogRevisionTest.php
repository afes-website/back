<?php

use App\Models\Article;
use App\Models\Revision;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use \Carbon\Carbon;
use PHPUnit\Framework\Assert as PHPUnit;

class BlogRevisionTest extends TestCase {
    public function test_get_all() {
        $revisions = [];
        $count = 5;

        for($i = 0; $i < $count; ++$i) {
            $revisions[] = factory(Revision::class)->create();
        }

        $admin_user = AdminAuthJwt::get_token($this);
        $this->get('/blog/revisions',
            ['X-ADMIN-TOKEN' => $admin_user['token']]);
        $this->assertResponseOk();
        $this->receiveJson();
        PHPUnit::assertCount($count, json_decode($this->response->getContent()));
    }

    public function test_list_filter() {
        $revisions = [];
        $count = 5;

        for($i = 0; $i < $count; ++$i) {
            $revisions[] = factory(Revision::class)->create();
        }
        $admin_user = AdminAuthJwt::get_token($this);
        foreach([
            "id",
            "title",
            "article_id",
            "user_id",
            "content",
            "status"
            ] as $key) {
            $this->call('GET', '/blog/revisions',
                [$key => $revisions[0]->{$key}],
                [],
                [],
                $this->transformHeadersToServerVars([
                    'X-ADMIN-TOKEN' => $admin_user['token']
                ]));
            $this->assertResponseOk();

            $this->receiveJson();
            $ret_revisions = json_decode($this->response->getContent());
            foreach($ret_revisions as $revision) {
                PHPUnit::assertEquals($revisions[0]->{$key}, $revision->{$key});
            }
        }
    }

    public function test_list_invalid_filter() {
        $admin_user = AdminAuthJwt::get_token($this);
        $this->call('GET', '/blog/revisions',
            ['id' => Str::random(8)],
            [],
            [],
            $this->transformHeadersToServerVars([
                'X-ADMIN-TOKEN' => $admin_user['token']
            ]));
        $this->assertResponseStatus(400);
    }

    public function test_list_writer() {
        $own_count = 3;
        $other_count = 5;
        $writer_user = WriterAuthJwt::get_token($this);

        for($i = 0; $i < $own_count; ++$i) {
            factory(Revision::class)->create([
                'user_id' => $writer_user['user']->id,
            ]);
        }
        for($i = 0; $i < $other_count; ++$i) {
            factory(Revision::class)->create();
        }

        $this->get("/blog/revisions",
            ['X-BLOG-WRITER-TOKEN' => $writer_user['token']]);
        $this->assertResponseOk();
        $this->receiveJson();
        PHPUnit::assertCount($own_count, json_decode($this->response->getContent()));
    }

    public function test_list_guest() {
        $this->get("/blog/revisions");
        $this->assertResponseStatus(401);
    }

    public function test_show() {
        $revision = factory(Revision::class)->create();
        $admin_user = AdminAuthJwt::get_token($this);
        $this->get("/blog/revisions/{$revision->id}",
            ['X-ADMIN-TOKEN' => $admin_user['token']]);
        $this->assertResponseOk();
        $this->receiveJson();

        $ret = json_decode($this->response->getContent());
        $this->seeJsonEquals([
            'id' => $revision->id,
            'title' => $revision->title,
            'article_id' => $revision->article_id,
            'user_id' => $revision->user_id,
            'timestamp' => $revision->timestamp->toIso8601ZuluString(),
            'content' => $revision->content,
            'status' => $revision->status
        ]);
    }

    public function test_show_not_found() {
        $admin_user = AdminAuthJwt::get_token($this);
        $this->get("/blog/revisions/1",
            ['X-ADMIN-TOKEN' => $admin_user['token']]);
        $this->assertResponseStatus(404);
    }

    public function test_show_writer() {
        $writer_user = WriterAuthJwt::get_token($this);
        $own_revision = factory(Revision::class)->create([
            'user_id' => $writer_user['user']->id
        ]);
        $other_revision = factory(Revision::class)->create();
        $this->get("/blog/revisions/{$own_revision->id}",
            ['X-BLOG-WRITER-TOKEN' => $writer_user['token']]);
        $this->assertResponseOk();
        $this->receiveJson();

        $this->get("/blog/revisions/{$other_revision->id}",
            ['X-BLOG-WRITER-TOKEN' => $writer_user['token']]);
        $this->assertResponseStatus(403);
    }

    public function test_show_guest() {
        $revision = factory(Revision::class)->create();
        $this->get("/blog/revisions/{$revision->id}");
        $this->assertResponseStatus(401);
    }
}
