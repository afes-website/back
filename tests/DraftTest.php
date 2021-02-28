<?php
namespace Tests;

use App\Models\Draft;
use App\Models\Exhibition;
use App\Models\Revision;
use Illuminate\Support\Str;
use Faker;

class DraftTest extends TestCase {
    public function testIndexAll() {
        $drafts = [];
        $exh = [];
        $count = 5;

        for ($i = 0; $i < $count; ++$i) {
            $exh[] = factory(Exhibition::class)->create();
            $drafts[] = factory(Draft::class)->create([
                'exh_id' => $exh[$i]->id
            ]);
        }

        foreach (['blogAdmin', 'teacher'] as $key) {
            $user = AuthJwt::getToken($this, [$key]);
            $this->get('/online/drafts/', $user['auth_hdr']);
            $this->assertResponseOk();
            $this->receiveJson();
            $this->assertCount($count, json_decode($this->response->getContent()));
        }
    }

    public function testListFilter() {
        $drafts = [];
        $count = 4;

        for ($i = 0; $i < $count; ++$i) {
            $exh_user = AuthJwt::getToken($this, ['exhibition']);
            $exh[] = factory(Exhibition::class)->create([
                'id' => $exh_user['user']->id
            ]);
            $drafts[] = factory(Draft::class)->create([
                'exh_id' => $exh_user['user']->id,
                'user_id' => $exh_user['user']->id
            ]);
        }
        $admin_user = AuthJwt::getToken($this, ['blogAdmin']);
        $this->get("/online/drafts/{$drafts[0]->id}", $admin_user['auth_hdr']);
        $this->assertResponseOk();
        $this->receiveJson();
        $draft = json_decode($this->response->getContent());
        foreach ([
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
            $this->call(
                'GET',
                '/blog/revisions',
                [$key => $draft->{$key}],
                [],
                [],
                $this->transformHeadersToServerVars($admin_user['auth_hdr'])
            );
            $this->assertResponseOk();

            $this->receiveJson();
            $ret_drafts = json_decode($this->response->getContent());
            foreach ($ret_drafts as $ret_draft) {
                $this->assertEquals($draft->{$key}, $ret_draft->{$key});
            }
        }

        // author_id
        $this->call(
            'GET',
            '/online/drafts',
            ['author_id' => $draft->author->id],
            [],
            [],
            $this->transformHeadersToServerVars($admin_user['auth_hdr'])
        );
        $this->assertResponseOk();

        $this->receiveJson();
        $ret_drafts = json_decode($this->response->getContent());
        foreach ($ret_drafts as $ret_draft) {
            $this->assertEquals(
                $draft->author->id,
                $ret_draft->author->id
            );
        }

        // exh_id
        $this->call(
            'GET',
            '/online/drafts',
            ['author_id' => $draft->author->id],
            [],
            [],
            $this->transformHeadersToServerVars($admin_user['auth_hdr'])
        );
        $this->assertResponseOk();

        $this->receiveJson();
        $ret_drafts = json_decode($this->response->getContent());
        foreach ($ret_drafts as $ret_draft) {
            $this->assertEquals(
                $draft->author->id,
                $ret_draft->author->id
            );
        }
    }

    public function testListWriter() {
        $drafts = [];
        $users = [];
        $count = 4;

        for ($i = 0; $i < $count; ++$i) {
            $users[] = AuthJwt::getToken($this, ['exhibition']);
            $exh[] = factory(Exhibition::class)->create([
                'id' => $users[$i]['user']->id
            ]);
            $drafts[] = factory(Draft::class)->create([
                'exh_id' => $users[$i]['user']->id,
                'user_id' => $users[$i]['user']->id
            ]);
        }
        $this->get("/online/drafts", $users[0]['auth_hdr']);
        $this->assertResponseOk();
        $this->receiveJson();
        $ret_drafts = json_decode($this->response->getContent());
        foreach ($ret_drafts as $draft) {
            $this->assertEquals(
                $draft->author->id,
                $drafts[0]->user_id
            );
        }
    }

    public function testShow() {
        $drafts = [];
        $exh = [];
        $count = 5;

        for ($i = 0; $i < $count; ++$i) {
            $exh[] = factory(Exhibition::class)->create();
            $drafts[] = factory(Draft::class)->create([
                'exh_id' => $exh[$i]->id
            ]);
        }

        foreach (['blogAdmin', 'teacher'] as $key) {
            $user = AuthJwt::getToken($this, [$key]);
            $this->get("/online/drafts/{$drafts[0]->id}", $user['auth_hdr']);
            $this->assertResponseOk();
        }
    }

    public function testShowNotFound() {
        $admin_user = AuthJwt::getToken($this, ['blogAdmin']);
        $this->get("/online/drafts/{Str::random(8)}", $admin_user['auth_hdr']);
        $this->assertResponseStatus(404);
    }

    public function testShowWriter() {
        $drafts = [];
        $users = [];
        $count = 4;

        for ($i = 0; $i < $count; ++$i) {
            $users[] = AuthJwt::getToken($this, ['exhibition']);
            $exh[] = factory(Exhibition::class)->create([
                'id' => $users[$i]['user']->id
            ]);
            $drafts[] = factory(Draft::class)->create([
                'exh_id' => $users[$i]['user']->id,
                'user_id' => $users[$i]['user']->id
            ]);
        }
        $this->get("/online/drafts/{$drafts[0]->id}", $users[0]['auth_hdr']);
        $this->assertResponseOk();

        for ($i = 1; $i < $count; ++$i) {
            $this->get("/online/drafts/{$drafts[$i]->id}", $users[$i]['auth_hdr']);
            $this->assertResponseStatus(403);
        }
    }


    public function testCreate() {
        $faker = Faker\Factory::create('ja_JP');
        $user = AuthJwt::getToken($this, ['blogAdmin']);
        $own_exh = factory(Exhibition::class)->create([
            'id' => $user['user']->id
        ]);

        $oth_exh = factory(Exhibition::class)->create();

        $this->post('/online/drafts/', [
            'exh_id' => $own_exh->id,
            'content' => $faker->paragraph(),
        ], $user['auth_hdr']);
        $this->assertResponseStatus(201);

        $this->post('/online/drafts/', [
            'exh_id' => $oth_exh->id,
            'content' => $faker->paragraph(),
        ], $user['auth_hdr']);
        $this->assertResponseStatus(201);
    }

    public function testCreateUser() {
        $faker = Faker\Factory::create('ja_JP');
        $user = AuthJwt::getToken($this, ['exhibition']);
        $own_exh = factory(Exhibition::class)->create([
            'id' => $user['user']->id
        ]);

        $oth_exh = factory(Exhibition::class)->create();

        $this->post('/online/drafts/', [
            'exh_id' => $own_exh->id,
            'content' => $faker->paragraph(),
        ], $user['auth_hdr']);
        $this->assertResponseStatus(201);

        $this->post('/online/drafts/', [
            'exh_id' => $oth_exh->id,
            'content' => $faker->paragraph(),
        ], $user['auth_hdr']);
        $this->assertResponseStatus(403);
    }

    public function testCreateFail() {
        $faker = Faker\Factory::create('ja_JP');
        $user = AuthJwt::getToken($this, ['blogAdmin']);

        $this->post('/online/drafts/', [
            'exh_id' => Str::random(8),
            'content' => $faker->paragraph(),
        ], $user['auth_hdr']);
        $this->assertResponseStatus(400);

        $faker = Faker\Factory::create('ja_JP');
        $user = AuthJwt::getToken($this, ['blogAdmin']);

        $this->post('/online/drafts/', [
            'exh_id' => Str::random(8),
        ], $user['auth_hdr']);
        $this->assertResponseStatus(400);

        $this->post('/online/drafts/', [
            'content' => $faker->paragraph(),
        ], $user['auth_hdr']);
        $this->assertResponseStatus(400);
    }

    public function testAccept() {
        $drafts = [];
        $exh = [];
        $count = 5;

        for ($i = 0; $i < $count; ++$i) {
            $exh[] = factory(Exhibition::class)->create();
            $drafts[] = factory(Draft::class)->create([
                'exh_id' => $exh[$i]->id
            ]);
        }

        foreach (['blogAdmin', 'teacher'] as $key) {
            $user = AuthJwt::getToken($this, [$key]);
            $this->patch("/online/drafts/{$drafts[0]->id}/accept", [], $user['auth_hdr']);
            $this->assertResponseOk();
            $this->receiveJson();
        }
    }

    public function testReject() {
        $drafts = [];
        $exh = [];
        $count = 5;

        for ($i = 0; $i < $count; ++$i) {
            $exh[] = factory(Exhibition::class)->create();
            $drafts[] = factory(Draft::class)->create([
                'exh_id' => $exh[$i]->id
            ]);
        }

        foreach (['blogAdmin', 'teacher'] as $key) {
            $user = AuthJwt::getToken($this, [$key]);
            $this->patch("/online/drafts/{$drafts[0]->id}/reject", [], $user['auth_hdr']);
            $this->assertResponseOk();
            $this->receiveJson();
        }
    }

    public function testComment() {
        $drafts = [];
        $exh = [];
        $count = 5;

        for ($i = 0; $i < $count; ++$i) {
            $exh[] = factory(Exhibition::class)->create();
            $drafts[] = factory(Draft::class)->create([
                'exh_id' => $exh[$i]->id
            ]);
        }
        $faker = Faker\Factory::create('ja_JP');

        foreach (['blogAdmin', 'teacher'] as $key) {
            $user = AuthJwt::getToken($this, [$key]);
            $this->post(
                "/online/drafts/{$drafts[0]->id}/comment",
                [
                    'comment' => $faker->paragraph()
                ],
                $user['auth_hdr']
            );
            $this->assertResponseOk();
            $this->receiveJson();
        }
    }

    public function testCommentFail() {
        $drafts = [];
        $exh = [];
        $count = 5;

        for ($i = 0; $i < $count; ++$i) {
            $exh[] = factory(Exhibition::class)->create();
            $drafts[] = factory(Draft::class)->create([
                'exh_id' => $exh[$i]->id
            ]);
        }
        $faker = Faker\Factory::create('ja_JP');

        foreach (['blogAdmin', 'teacher'] as $key) {
            $user = AuthJwt::getToken($this, [$key]);
            $this->post(
                "/online/drafts/{Str::random(8)}/comment",
                [
                    'comment' => $faker->paragraph()
                ],
                $user['auth_hdr']
            );
            $this->assertResponseStatus(404);
        }

        foreach (['blogAdmin', 'teacher'] as $key) {
            $user = AuthJwt::getToken($this, [$key]);
            $this->post(
                "/online/drafts/{$drafts[0]->id}/comment",
                $user['auth_hdr']
            );
            $this->assertResponseStatus(400);
        }
    }

    public function testPublishFail() {
        // NOTFOUND
        // NOT APPROVED
        $drafts = [];
        $exh = [];
        $count = 5;

        for ($i = 0; $i < $count; ++$i) {
            $exh[] = factory(Exhibition::class)->create();
            $drafts[] = factory(Draft::class)->create([
                'exh_id' => $exh[$i]->id
            ]);
        }

        foreach (['blogAdmin', 'teacher'] as $key) {
            $user = AuthJwt::getToken($this, [$key]);
            $this->patch(
                "/online/drafts/{Str::random(8)}/publish",
                [],
                $user['auth_hdr']
            );
            $this->assertResponseStatus(404);

            $this->patch(
                "/online/drafts/{$drafts[0]->id}/publish",
                [],
                $user['auth_hdr']
            );
            $this->assertResponseStatus(400);
        }
    }

    public function testGuest() {
        $drafts = [];
        $exh = [];
        $count = 3;

        for ($i = 0; $i < $count; ++$i) {
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
            ],
        ];

        foreach ($paths as $path) {
            $response = $this->json($path['method'], $path['path'], []);
            $response->assertResponseStatus(401);
        }
    }
}
