<?php
namespace Tests;

use App\Models\Article;
use App\Models\Revision;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use \Carbon\Carbon;
use Faker;

class BlogRevisionTest extends TestCase {
    public function testGetAll() {
        $revisions = [];
        $count = 5;
        $user = User::factory()->permission('blogWriter')->create();

        for ($i = 0; $i < $count; ++$i) {
            $revisions[] = factory(Revision::class)->create([
                'user_id' => $user->id,
            ]);
        }

        $admin_user = User::factory()->permission('blogAdmin')->create();
        $this->actingAs($admin_user)->get(
            '/blog/revisions'
        );
        $this->assertResponseOk();
        $this->assertJson($this->response->getContent());
        $this->assertCount($count, json_decode($this->response->getContent()));
    }

    public function testListFilter() {
        $revisions = [];
        $count = 5;
        $writer_user = User::factory()->permission('blogWriter')->create();

        for ($i = 0; $i < $count; ++$i) {
            $revisions[] = factory(Revision::class)->create([
                'user_id' => $writer_user->id
            ]);
        }
        $admin_user = User::factory()->permission('blogAdmin')->create();
        foreach ([
            "id",
            "title",
            "article_id",
            "content",
            "status",
            "handle_name",
        ] as $key) {
            $this->actingAs($admin_user)->call(
                'GET',
                '/blog/revisions',
                [$key => $revisions[0]->{$key}],
                [],
                []
            );
            $this->assertResponseOk();

            $this->assertJson($this->response->getContent());
            $ret_revisions = json_decode($this->response->getContent());
            foreach ($ret_revisions as $revision) {
                $this->assertEquals($revisions[0]->{$key}, $revision->{$key});
            }
        }

        // author_id
        $this->actingAs($admin_user)->call(
            'GET',
            '/blog/revisions',
            ['author_id' => $revisions[0]['user_id']],
            [],
            []
        );
        $this->assertResponseOk();

        $this->assertJson($this->response->getContent());
        $ret_revisions = json_decode($this->response->getContent());
        foreach ($ret_revisions as $revision) {
            $this->assertEquals(
                $revisions[0]['user_id'],
                $revision->author->id
            );
        }
    }

    public function testListInvalidFilter() {
        $admin_user = User::factory()->permission('blogAdmin')->create();
        $this->actingAs($admin_user)->call(
            'GET',
            '/blog/revisions',
            ['id' => Str::random(8)],
            [],
            []
        );
        $this->assertResponseStatus(400);
    }

    public function testListWriter() {
        $own_count = 3;
        $other_count = 5;
        $writer_user = User::factory()->permission('blogWriter')->create();

        for ($i = 0; $i < $own_count; ++$i) {
            factory(Revision::class)->create([
                'user_id' => $writer_user->id,
            ]);
        }
        for ($i = 0; $i < $other_count; ++$i) {
            $other_user = User::factory()->permission('blogWriter')->create();
            factory(Revision::class)->create([
                'user_id' => $other_user->id,
            ]);
        }

        $this->actingAs($writer_user)->get(
            "/blog/revisions"
        );
        $this->assertResponseOk();
        $this->assertJson($this->response->getContent());
        $this->assertCount($own_count, json_decode($this->response->getContent()));
    }

    public function testListGuest() {
        $this->get("/blog/revisions");
        $this->assertResponseStatus(401);
    }

    public function testShow() {
        $writer_user = User::factory()->permission('blogWriter')->create();
        $revision = factory(Revision::class)->create([
            'user_id' => $writer_user->id,
        ]);
        $admin_user = User::factory()->permission('blogAdmin')->create();
        $this->actingAs($admin_user)->get(
            "/blog/revisions/{$revision->id}"
        );
        $this->assertResponseOk();
        $this->assertJson($this->response->getContent());

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
            'user_id' => $writer_user->id,
            'handle_name' => null,
        ]);
        $this->actingAs($admin_user)->get(
            "/blog/revisions/{$revision->id}"
        );
        $this->assertResponseOk();
        $this->assertJson($this->response->getContent());

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

    public function testShowNotFound() {
        $admin_user = User::factory()->permission('blogAdmin')->create();
        $this->actingAs($admin_user)->get(
            "/blog/revisions/1"
        );
        $this->assertResponseStatus(404);
    }

    public function testShowWriter() {
        $writer_user = User::factory()->permission('blogWriter')->create();
        $own_revision = factory(Revision::class)->create([
            'user_id' => $writer_user->id
        ]);
        $other_user = User::factory()->permission('blogWriter')->create();
        $other_revision = factory(Revision::class)->create([
            'user_id' => $other_user->id
        ]);
        $this->actingAs($writer_user)->get(
            "/blog/revisions/{$own_revision->id}"
        );
        $this->assertResponseOk();
        $this->assertJson($this->response->getContent());

        $this->actingAs($writer_user)->get(
            "/blog/revisions/{$other_revision->id}"
        );
        $this->assertResponseStatus(403);
    }

    public function testShowGuest() {
        $writer_user = User::factory()->permission('blogWriter')->create();
        $revision = factory(Revision::class)->create([
            'user_id' => $writer_user->id,
        ]);
        $this->get("/blog/revisions/{$revision->id}");
        $this->assertResponseStatus(401);
    }

    public function testCreate() {
        $faker = Faker\Factory::create('ja_JP');
        $writer_user = User::factory()->permission('blogWriter')->create();
        $this->actingAs($writer_user)->json(
            'POST',
            '/blog/revisions',
            [
                'title' => $faker->sentence(10),
                'article_id' => Str::random(32),
                'content' => $faker->paragraph(),
            ]
        );
        $this->assertResponseStatus(201);
        $this->assertJson($this->response->getContent());
        $res = json_decode($this->response->getContent());
        $obj = Revision::find($res->id);
        foreach (['title', 'article_id', 'content', 'status'] as $attr) {
            $this->assertEquals($obj->$attr, $res->$attr);
        }
    }

    public function testCreateFail() {
        $faker = Faker\Factory::create('ja_JP');
        $writer_user = User::factory()->permission('blogWriter')->create();
        foreach (['title', 'article_id', 'content'] as $removal) {
            $post_data = [];
            if ($removal!=='title') $post_data['title'] = $faker->sentence(10);
            if ($removal!=='article_id') $post_data['article_id'] = Str::random(32);
            if ($removal!=='content') $post_data['content'] = $faker->paragraph();
            $this->actingAs($writer_user)->json(
                'POST',
                '/blog/revisions',
                $post_data
            );
            $this->assertResponseStatus(400);
        }
    }

    public function testCreateGuest() {
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
        $admin_user = User::factory()->permission('blogAdmin')->create();
        $this->actingAs($admin_user)->json(
            'POST',
            '/blog/revisions',
            [
                'title' => $faker->sentence(10),
                'article_id' => Str::random(32),
                'content' => $faker->paragraph(),
            ]
        );
        $this->assertResponseStatus(403);
    }

    public function testCreateInvalidPath() {
        $faker = Faker\Factory::create('ja_JP');
        $writer_user = User::factory()->permission('blogWriter')->create();
        $this->actingAs($writer_user)->json(
            'POST',
            '/blog/revisions',
            [
                'title' => $faker->sentence(10),
                'article_id' => Str::random(32) . '!', // ! is invalid character
                'content' => $faker->paragraph(),
            ]
        );
        $this->assertResponseStatus(400);
    }

    public function testAccept() {
        $admin_user = User::factory()->permission('blogAdmin')->create();
        $writer_user = User::factory()->permission('blogWriter')->create();
        $revision = factory(Revision::class)->create([
            'user_id' => $writer_user->id,
        ]);

        $this->actingAs($admin_user)->patch(
            "/blog/revisions/{$revision->id}/accept",
            []
        );
        $this->assertResponseOk();
        $this->assertJson($this->response->getContent());

        $revision = Revision::find($revision->id); // reload
        $this->assertEquals('accepted', $revision->status);
    }

    public function testReject() {
        $admin_user = User::factory()->permission('blogAdmin')->create();
        $writer_user = User::factory()->permission('blogWriter')->create();
        $revision = factory(Revision::class)->create([
            'user_id' => $writer_user->id,
        ]);

        $this->actingAs($admin_user)->patch(
            "/blog/revisions/{$revision->id}/reject",
            []
        );
        $this->assertResponseOk();
        $this->assertJson($this->response->getContent());

        $revision = Revision::find($revision->id); // reload
        $this->assertEquals('rejected', $revision->status);
    }

    public function testStatusGuest() {
        $writer_user = User::factory()->permission('blogWriter')->create();
        $revision = factory(Revision::class)->create([
            'user_id' => $writer_user->id,
        ]);

        $this->patch("/blog/revisions/{$revision->id}/accept");
        $this->assertResponseStatus(401);
        $revision = Revision::find($revision->id); // reload
        $this->assertEquals('waiting', $revision->status);

        $this->patch("/blog/revisions/{$revision->id}/reject");
        $this->assertResponseStatus(401);
        $revision = Revision::find($revision->id); // reload
        $this->assertEquals('waiting', $revision->status);

        // blog writer also cannot change status
        $writer_user = User::factory()->permission('blogWriter')->create();

        $this->actingAs($writer_user)->patch(
            "/blog/revisions/{$revision->id}/accept",
            []
        );
        $this->assertResponseStatus(403);
        $revision = Revision::find($revision->id); // reload
        $this->assertEquals('waiting', $revision->status);

        $this->actingAs($writer_user)->patch(
            "/blog/revisions/{$revision->id}/reject",
            []
        );
        $this->assertResponseStatus(403);
        $revision = Revision::find($revision->id); // reload
        $this->assertEquals('waiting', $revision->status);
    }

    public function testContribCreate() {
        $faker = Faker\Factory::create('ja_JP');
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

    public function testContribCreateFail() {
        $faker = Faker\Factory::create('ja_JP');
        $writer_user = User::factory()->permission('blogWriter')->create();
        $this->json(
            'POST',
            '/blog/revisions/contrib',
            [
                'title' => $faker->sentence(10),
            ]
        );
        $this->assertResponseStatus(400);

        $faker = Faker\Factory::create('ja_JP');
        $writer_user = User::factory()->permission('blogWriter')->create();
        $this->actingAs($writer_user)->json(
            'POST',
            '/blog/revisions/contrib',
            [
                'content' => $faker->paragraph(),
            ]
        );
        $this->assertResponseStatus(400);
    }
}
