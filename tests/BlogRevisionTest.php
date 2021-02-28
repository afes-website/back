<?php

use App\Models\Article;
use App\Models\Revision;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use \Carbon\Carbon;

class BlogRevisionTest extends TestCase {
    public function test_get_all() {
        $revisions = [];
        $count = 5;

        for ($i = 0; $i < $count; ++$i) {
            $revisions[] = factory(Revision::class)->create();
        }

        $admin_user = AuthJwt::get_token($this, ['blogAdmin']);
        $this->get(
            '/blog/revisions',
            $admin_user['auth_hdr']
        );
        $this->assertResponseOk();
        $this->receiveJson();
        $this->assertCount($count, json_decode($this->response->getContent()));
    }

    public function test_list_filter() {
        $revisions = [];
        $count = 5;
        $writer_user = AuthJwt::get_token($this, ['blogWriter']);

        for ($i = 0; $i < $count; ++$i) {
            $revisions[] = factory(Revision::class)->create([
                'user_id' => $writer_user['user']->id
            ]);
        }
        $admin_user = AuthJwt::get_token($this, ['blogAdmin']);
        foreach ([
            "id",
            "title",
            "article_id",
            "content",
            "status",
            "handle_name",
        ] as $key) {
            $this->call(
                'GET',
                '/blog/revisions',
                [$key => $revisions[0]->{$key}],
                [],
                [],
                $this->transformHeadersToServerVars($admin_user['auth_hdr'])
            );
            $this->assertResponseOk();

            $this->receiveJson();
            $ret_revisions = json_decode($this->response->getContent());
            foreach ($ret_revisions as $revision) {
                $this->assertEquals($revisions[0]->{$key}, $revision->{$key});
            }
        }

        // author_id
        $this->call(
            'GET',
            '/blog/revisions',
            ['author_id' => $revisions[0]['user_id']],
            [],
            [],
            $this->transformHeadersToServerVars($admin_user['auth_hdr'])
        );
        $this->assertResponseOk();

        $this->receiveJson();
        $ret_revisions = json_decode($this->response->getContent());
        foreach ($ret_revisions as $revision) {
            $this->assertEquals(
                $revisions[0]['user_id'],
                $revision->author->id
            );
        }
    }

    public function test_list_invalid_filter() {
        $admin_user = AuthJwt::get_token($this, ['blogAdmin']);
        $this->call(
            'GET',
            '/blog/revisions',
            ['id' => Str::random(8)],
            [],
            [],
            $this->transformHeadersToServerVars($admin_user['auth_hdr'])
        );
        $this->assertResponseStatus(400);
    }

    public function test_list_writer() {
        $own_count = 3;
        $other_count = 5;
        $writer_user = AuthJwt::get_token($this, ['blogWriter']);

        for ($i = 0; $i < $own_count; ++$i) {
            factory(Revision::class)->create([
                'user_id' => $writer_user['user']->id,
            ]);
        }
        for ($i = 0; $i < $other_count; ++$i) {
            factory(Revision::class)->create();
        }

        $this->get(
            "/blog/revisions",
            $writer_user['auth_hdr']
        );
        $this->assertResponseOk();
        $this->receiveJson();
        $this->assertCount($own_count, json_decode($this->response->getContent()));
    }

    public function test_list_guest() {
        $this->get("/blog/revisions");
        $this->assertResponseStatus(401);
    }

    public function test_show() {
        $revision = factory(Revision::class)->create();
        $admin_user = AuthJwt::get_token($this, ['blogAdmin']);
        $this->get(
            "/blog/revisions/{$revision->id}",
            $admin_user['auth_hdr']
        );
        $this->assertResponseOk();
        $this->receiveJson();

        $ret = json_decode($this->response->getContent());
        $this->seeJsonEquals([
            'id' => $revision->id,
            'title' => $revision->title,
            'article_id' => $revision->article_id,
            'author' => $revision->user,
            'timestamp' => $revision->timestamp->toIso8601ZuluString(),
            'content' => $revision->content,
            'status' => $revision->status,
            'handle_name' => $revision->handle_name
        ]);

        $revision = factory(Revision::class)->create([
            'handle_name' => null
        ]);
        $this->get(
            "/blog/revisions/{$revision->id}",
            $admin_user['auth_hdr']
        );
        $this->assertResponseOk();
        $this->receiveJson();

        $ret = json_decode($this->response->getContent());
        $this->seeJsonEquals([
            'id' => $revision->id,
            'title' => $revision->title,
            'article_id' => $revision->article_id,
            'author' => $revision->user,
            'timestamp' => $revision->timestamp->toIso8601ZuluString(),
            'content' => $revision->content,
            'status' => $revision->status,
            'handle_name' => null
        ]);
    }

    public function test_show_not_found() {
        $admin_user = AuthJwt::get_token($this, ['blogAdmin']);
        $this->get(
            "/blog/revisions/1",
            $admin_user['auth_hdr']
        );
        $this->assertResponseStatus(404);
    }

    public function test_show_writer() {
        $writer_user = AuthJwt::get_token($this, ['blogWriter']);
        $own_revision = factory(Revision::class)->create([
            'user_id' => $writer_user['user']->id
        ]);
        $other_revision = factory(Revision::class)->create();
        $this->get(
            "/blog/revisions/{$own_revision->id}",
            $writer_user['auth_hdr']
        );
        $this->assertResponseOk();
        $this->receiveJson();

        $this->get(
            "/blog/revisions/{$other_revision->id}",
            $writer_user['auth_hdr']
        );
        $this->assertResponseStatus(403);
    }

    public function test_show_guest() {
        $revision = factory(Revision::class)->create();
        $this->get("/blog/revisions/{$revision->id}");
        $this->assertResponseStatus(401);
    }

    public function test_create() {
        $faker = Faker\Factory::create('ja_JP');
        $writer_user = AuthJwt::get_token($this, ['blogWriter']);
        $this->json(
            'POST',
            '/blog/revisions',
            [
                'title' => $faker->sentence(10),
                'article_id' => Str::random(32),
                'content' => $faker->paragraph(),
            ],
            $writer_user['auth_hdr']
        );
        $this->assertResponseStatus(201);
        $this->receiveJson();
        $res = json_decode($this->response->getContent());
        $obj = Revision::find($res->id);
        foreach (['title', 'article_id', 'content', 'status'] as $attr) {
            $this->assertEquals($obj->$attr, $res->$attr);
        }
    }

    public function test_create_fail() {
        $faker = Faker\Factory::create('ja_JP');
        $writer_user = AuthJwt::get_token($this, ['blogWriter']);
        foreach (['title', 'article_id', 'content'] as $removal) {
            $post_data = [];
            if ($removal!=='title') $post_data['title'] = $faker->sentence(10);
            if ($removal!=='article_id') $post_data['article_id'] = Str::random(32);
            if ($removal!=='content') $post_data['content'] = $faker->paragraph();
            $this->json(
                'POST',
                '/blog/revisions',
                $post_data,
                $writer_user['auth_hdr']
            );
            $this->assertResponseStatus(400);
        }
    }

    public function test_create_guest() {
        $faker = Faker\Factory::create('ja_JP');
        $this->json(
            'POST',
            '/blog/revisions',
            [
                'title' => $faker->sentence(10),
                'article_id' => Str::random(32),
                'content' => $faker->paragraph(),
            ]
        );
        $this->assertResponseStatus(401);

        // admin also cannot create revision
        $admin_user = AuthJwt::get_token($this, ['blogAdmin']);
        $this->json(
            'POST',
            '/blog/revisions',
            [
                'title' => $faker->sentence(10),
                'article_id' => Str::random(32),
                'content' => $faker->paragraph(),
            ],
            $admin_user['auth_hdr']
        );
        $this->assertResponseStatus(403);
    }

    public function test_create_invalid_path() {
        $faker = Faker\Factory::create('ja_JP');
        $writer_user = AuthJwt::get_token($this, ['blogWriter']);
        $this->json(
            'POST',
            '/blog/revisions',
            [
                'title' => $faker->sentence(10),
                'article_id' => Str::random(32) . '!', // ! is invalid character
                'content' => $faker->paragraph(),
            ],
            $writer_user['auth_hdr']
        );
        $this->assertResponseStatus(400);
    }

    public function test_accept() {
        $admin_user = AuthJwt::get_token($this, ['blogAdmin']);
        $revision = factory(Revision::class)->create();

        $this->patch(
            "/blog/revisions/{$revision->id}/accept",
            [],
            $admin_user['auth_hdr']
        );
        $this->assertResponseOk();
        $this->receiveJson();

        $revision = Revision::find($revision->id); // reload
        $this->assertEquals('accepted', $revision->status);
    }

    public function test_reject() {
        $admin_user = AuthJwt::get_token($this, ['blogAdmin']);
        $revision = factory(Revision::class)->create();

        $this->patch(
            "/blog/revisions/{$revision->id}/reject",
            [],
            $admin_user['auth_hdr']
        );
        $this->assertResponseOk();
        $this->receiveJson();

        $revision = Revision::find($revision->id); // reload
        $this->assertEquals('rejected', $revision->status);
    }

    public function test_status_guest() {
        $revision = factory(Revision::class)->create();

        $this->patch("/blog/revisions/{$revision->id}/accept");
        $this->assertResponseStatus(401);
        $revision = Revision::find($revision->id); // reload
        $this->assertEquals('waiting', $revision->status);

        $this->patch("/blog/revisions/{$revision->id}/reject");
        $this->assertResponseStatus(401);
        $revision = Revision::find($revision->id); // reload
        $this->assertEquals('waiting', $revision->status);

        // blog writer also cannot change status
        $writer_user = AuthJwt::get_token($this, ['blogWriter']);

        $this->patch(
            "/blog/revisions/{$revision->id}/accept",
            [],
            $writer_user['auth_hdr']
        );
        $this->assertResponseStatus(403);
        $revision = Revision::find($revision->id); // reload
        $this->assertEquals('waiting', $revision->status);

        $this->patch(
            "/blog/revisions/{$revision->id}/reject",
            [],
            $writer_user['auth_hdr']
        );
        $this->assertResponseStatus(403);
        $revision = Revision::find($revision->id); // reload
        $this->assertEquals('waiting', $revision->status);
    }

    public function test_contrib_create() {
        $faker = Faker\Factory::create('ja_JP');
        $writer_user = AuthJwt::get_token($this, ['blogWriter']);
        $this->json(
            'POST',
            '/blog/revisions/contrib',
            [
                'title' => $faker->sentence(10),
                'content' => $faker->paragraph(),
            ]
        );
        $this->assertResponseStatus(201);
    }

    public function test_contrib_create_fail() {
        $faker = Faker\Factory::create('ja_JP');
        $writer_user = AuthJwt::get_token($this, ['blogWriter']);
        $this->json(
            'POST',
            '/blog/revisions/contrib',
            [
                'title' => $faker->sentence(10),
            ]
        );
        $this->assertResponseStatus(400);

        $faker = Faker\Factory::create('ja_JP');
        $writer_user = AuthJwt::get_token($this, ['blogWriter']);
        $this->json(
            'POST',
            '/blog/revisions/contrib',
            [
                'content' => $faker->paragraph(),
            ]
        );
        $this->assertResponseStatus(400);
    }
}
