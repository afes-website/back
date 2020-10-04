<?php

use App\Models\Draft;
use App\Models\Exhibition;
use App\Models\Revision;
use Illuminate\Support\Str;

class DraftTest extends TestCase {
    public function test_index_all() {
        $drafts = [];
        $exh = [];
        $count = 5;

        for($i = 0; $i < $count; ++$i) {
            $exh[] = factory(Exhibition::class)->create();
            $drafts[] = factory(Draft::class)->create([
                'exh_id' => $exh[$i]->id
            ]);
        }

        foreach(['blogAdmin', 'teacher'] as $key) {
            $user = AuthJwt::get_token($this, [$key]);
            $this->get('/online/drafts/', $user['auth_hdr']);
            $this->assertResponseOk();
            $this->receiveJson();
            $this->assertCount($count, json_decode($this->response->getContent()));
        }
    }

    public function test_list_filter() {
        $drafts = [];
        $count = 4;

        for($i = 0; $i < $count; ++$i) {
            $exh_user = AuthJwt::get_token($this, ['exhibition']);
            $exh[] = factory(Exhibition::class)->create([
                'id' => $exh_user['user']->id
            ]);
            $drafts[] = factory(Draft::class)->create([
                'exh_id' => $exh_user['user']->id,
                'user_id' => $exh_user['user']->id
            ]);
        }
        $admin_user = AuthJwt::get_token($this, ['blogAdmin']);
        $this->get("/online/drafts/{$drafts[0]->id}", $admin_user['auth_hdr']);
        $this->assertResponseOk();
        $this->receiveJson();
        $draft = json_decode($this->response->getContent());
        foreach(
            [
                "id",
                "content",
                "review_status",
                "teacher_review_status",
                "status",
                "published",
                "deleted",
                "created_at",
            ] as $key
        ) {
            $this->call('GET', '/blog/revisions',
                [$key => $draft->{$key}],
                [],
                [],
                $this->transformHeadersToServerVars($admin_user['auth_hdr']));
            $this->assertResponseOk();

            $this->receiveJson();
            $ret_drafts = json_decode($this->response->getContent());
            foreach($ret_drafts as $ret_draft) {
                $this->assertEquals($draft->{$key}, $ret_draft->{$key});
            }
        }

        // author_id
        $this->call('GET', '/online/drafts',
            ['author_id' => $draft->author->id],
            [],
            [],
            $this->transformHeadersToServerVars($admin_user['auth_hdr']));
        $this->assertResponseOk();

        $this->receiveJson();
        $ret_drafts = json_decode($this->response->getContent());
        foreach($ret_drafts as $ret_draft) {
            $this->assertEquals(
                $draft->author->id,
                $ret_draft->author->id);
        }

        // exh_id
        $this->call('GET', '/online/drafts',
            ['author_id' => $draft->author->id],
            [],
            [],
            $this->transformHeadersToServerVars($admin_user['auth_hdr']));
        $this->assertResponseOk();

        $this->receiveJson();
        $ret_drafts = json_decode($this->response->getContent());
        foreach($ret_drafts as $ret_draft) {
            $this->assertEquals(
                $draft->author->id,
                $ret_draft->author->id);
        }
    }

    public function test_list_writer() {
        // 除外検知
    }

    public function test_show() {

    }

    public function test_show_not_found() {

    }

    public function test_show_writer() {
        // 403 check
    }


    public function test_create() {

    }

    public function test_create_fail() {
        // Exh 404
        // Invalid Path
    }

    public function test_accept() {
        // teacher, exh
    }

    public function test_reject() {

    }

    public function test_comment() {
        // teacher, admin, exh
    }

    public function test_comment_fail() {
        // notFound
    }

    public function test_comment_guest() {

    }

    public function test_publish_fail() {
        // NOTFOUND
        // NOT APPROVED
    }

    public function test_guest() {
        $drafts = [];
        $exh = [];
        $count = 3;

        for($i = 0; $i < $count; ++$i) {
            $exh[] = factory(Exhibition::class)->create();
            $drafts[] = factory(Draft::class)->create([
                'exh_id' => $exh[$i]->id
            ]);
        }

        // GET

        $draft_path = "/online/drafts/{$drafts[0]->id}";
        $paths = [
            [
                'path' => '/online/drafts',
                'method' => 'GET',
            ],
            [
                'path' => '/online/drafts',
                'method' => 'POST',
            ],
            [
                'path' => $draft_path,
                'method' => 'GET'
            ],
            [
                'path' => "{$draft_path}/accept",
                'method' => 'PATCH'
            ],
            [
                'path' => "{$draft_path}/reject",
                'method' => 'PATCH'
            ],
            [
                'path' => "{$draft_path}/publish",
                'method' => 'PATCH'
            ],
            [
                'path' => "{$draft_path}/comment",
                'method' => 'POST'
            ]
        ];

        foreach($paths as $path) {
            $response = $this->json($path['method'], $path['path'], []);
            $response->assertResponseStatus(401);
        }
    }

    public function test_forbidden() {

    }
}
