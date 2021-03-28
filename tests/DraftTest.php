<?php
namespace Tests;

use App\Models\Draft;
use App\Models\Exhibition;
use App\Models\Revision;
use App\Models\User;
use App\Models\Image;
use Illuminate\Support\Str;
use Faker;

class DraftTest extends TestCase {
    public function testIndexAll() {
        $drafts = [];
        $exh = [];
        $count = 5;

        for ($i = 0; $i < $count; ++$i) {
            $user = factory(User::class)->states('exhibition')->create();
            $image = factory(Image::class)->create(['user_id' => $user->id]);
            $exh[] = factory(Exhibition::class)->create([
                'thumbnail_image_id' => $image->id,
            ]);
            $drafts[] = factory(Draft::class)->create([
                'user_id' => $user->id,
                'exh_id' => $exh[$i]->id,
            ]);
        }

        foreach (['blogAdmin', 'teacher'] as $key) {
            $user = factory(User::class)->states($key)->create([]);
            $this->actingAs($user)->get('/online/drafts/');
            $this->assertResponseOk();
            $this->receiveJson();
            $this->assertCount($count, json_decode($this->response->getContent()));
        }
    }

    public function testListFilter() {
        $drafts = [];
        $count = 4;

        for ($i = 0; $i < $count; ++$i) {
            $exh_user = factory(User::class)->states('exhibition')->create([]);
            $image = factory(Image::class)->create(['user_id' => $exh_user->id]);
            $exh[] = factory(Exhibition::class)->create([
                'id' => $exh_user->id,
                'thumbnail_image_id' => $image->id,
            ]);
            $drafts[] = factory(Draft::class)->create([
                'exh_id' => $exh_user->id,
                'user_id' => $exh_user->id
            ]);
        }
        $admin_user = factory(User::class)->states('blogAdmin')->create([]);
        $this->actingAs($admin_user)->get("/online/drafts/{$drafts[0]->id}");
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
            $this->actingAs($admin_user)->call(
                'GET',
                '/blog/revisions',
                [$key => $draft->{$key}],
                [],
                []
            );
            $this->assertResponseOk();

            $this->receiveJson();
            $ret_drafts = json_decode($this->response->getContent());
            foreach ($ret_drafts as $ret_draft) {
                $this->assertEquals($draft->{$key}, $ret_draft->{$key});
            }
        }

        // author_id
        $this->actingAs($admin_user)->call(
            'GET',
            '/online/drafts',
            ['author_id' => $draft->author->id],
            [],
            []
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
        $this->actingAs($admin_user)->call(
            'GET',
            '/online/drafts',
            ['author_id' => $draft->author->id],
            [],
            []
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
            $users[] = factory(User::class)->states('exhibition')->create([]);
            $image = factory(Image::class)->create(['user_id' => $users[$i]->id]);
            $exh[] = factory(Exhibition::class)->create([
                'id' => $users[$i]->id,
                'thumbnail_image_id' => $image->id,
            ]);
            $drafts[] = factory(Draft::class)->create([
                'exh_id' => $users[$i]->id,
                'user_id' => $users[$i]->id
            ]);
        }
        $this->actingAs($users[0])->get("/online/drafts");
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
            $user = factory(User::class)->create();
            $image = factory(Image::class)->create(['user_id' => $user->id]);
            $exh[] = factory(Exhibition::class)->create([
                'thumbnail_image_id' => $image->id,
            ]);
            $drafts[] = factory(Draft::class)->create([
                'exh_id' => $exh[$i]->id,
                'user_id' => $user->id,
            ]);
        }

        foreach (['blogAdmin', 'teacher'] as $key) {
            $user = factory(User::class)->states($key)->create([]);
            $this->actingAs($user)->get("/online/drafts/{$drafts[0]->id}");
            $this->assertResponseOk();
        }
    }

    public function testShowNotFound() {
        $admin_user = factory(User::class)->states('blogAdmin')->create([]);
        $this->actingAs($admin_user)->get("/online/drafts/{Str::random(8)}");
        $this->assertResponseStatus(404);
    }

    public function testShowWriter() {
        $drafts = [];
        $users = [];
        $count = 4;
        $users = factory(User::class, $count)->states('exhibition')->create([]);

        for ($i = 0; $i < $count; ++$i) {
            $image = factory(Image::class)->create(['user_id' => $users[$i]->id]);
            $exh[] = factory(Exhibition::class)->create([
                'id' => $users[$i]->id,
                'thumbnail_image_id' => $image->id,
            ]);
            $drafts[] = factory(Draft::class)->create([
                'exh_id' => $users[$i]->id,
                'user_id' => $users[$i]->id
            ]);
        }
        $this->actingAs($users[0])->get("/online/drafts/{$drafts[0]->id}");
        $this->assertResponseOk();

        for ($i = 1; $i < $count; ++$i) {
            $this->actingAs($users[$i])->get("/online/drafts/{$drafts[0]->id}");
            $this->assertResponseStatus(403);
        }
    }


    public function testCreate() {
        $faker = Faker\Factory::create('ja_JP');
        $user = factory(User::class)->states('blogAdmin')->create([]);
        $image = factory(Image::class)->create(['user_id' => $user->id]);
        $own_exh = factory(Exhibition::class)->create([
            'id' => $user->id,
            'thumbnail_image_id' => $image->id,
        ]);

        $oth_user = factory(User::class)->create();
        $oth_image = factory(Image::class)->create(['user_id' => $oth_user->id]);
        $oth_exh = factory(Exhibition::class)->create([
            'thumbnail_image_id' => $oth_image->id,
        ]);

        $this->actingAs($user)->post('/online/drafts/', [
            'exh_id' => $own_exh->id,
            'content' => $faker->paragraph(),
        ]);
        $this->assertResponseStatus(201);

        $this->actingAs($user)->post('/online/drafts/', [
            'exh_id' => $oth_exh->id,
            'content' => $faker->paragraph(),
        ]);
        $this->assertResponseStatus(201);
    }

    public function testCreateUser() {
        $faker = Faker\Factory::create('ja_JP');
        $user = factory(User::class)->states('exhibition')->create([]);
        $image = factory(Image::class)->create(['user_id' => $user->id]);
        $own_exh = factory(Exhibition::class)->create([
            'id' => $user->id,
            'thumbnail_image_id' => $image->id,
        ]);

        $oth_user = factory(User::class)->create();
        $image = factory(Image::class)->create(['user_id' => $oth_user->id]);
        $oth_exh = factory(Exhibition::class)->create([
            'thumbnail_image_id' => $image->id,
        ]);

        $this->actingAs($user)->post('/online/drafts/', [
            'exh_id' => $own_exh->id,
            'content' => $faker->paragraph(),
        ]);
        $this->assertResponseStatus(201);

        $this->actingAs($user)->post('/online/drafts/', [
            'exh_id' => $oth_exh->id,
            'content' => $faker->paragraph(),
        ]);
        $this->assertResponseStatus(403);
    }

    public function testCreateFail() {
        $faker = Faker\Factory::create('ja_JP');
        $user = factory(User::class)->states('blogAdmin')->create([]);

        $this->actingAs($user)->post('/online/drafts/', [
            'exh_id' => Str::random(8),
            'content' => $faker->paragraph(),
        ]);
        $this->assertResponseStatus(400);

        $faker = Faker\Factory::create('ja_JP');
        $user = factory(User::class)->states('blogAdmin')->create([]);

        $this->actingAs($user)->post('/online/drafts/', [
            'exh_id' => Str::random(8),
        ]);
        $this->assertResponseStatus(400);

        $this->actingAs($user)->post('/online/drafts/', [
            'content' => $faker->paragraph(),
        ]);
        $this->assertResponseStatus(400);
    }

    public function testAccept() {
        $drafts = [];
        $exh = [];
        $count = 5;

        for ($i = 0; $i < $count; ++$i) {
            $user = factory(User::class)->create();
            $image = factory(Image::class)->create(['user_id' => $user->id]);
            $exh[] = factory(Exhibition::class)->create([
                'thumbnail_image_id' => $image->id,
            ]);
            $drafts[] = factory(Draft::class)->create([
                'exh_id' => $exh[$i]->id,
                'user_id' => $user->id,
            ]);
        }

        foreach (['blogAdmin', 'teacher'] as $key) {
            $user = factory(User::class)->states($key)->create([]);
            $this->actingAs($user)->patch("/online/drafts/{$drafts[0]->id}/accept", []);
            $this->assertResponseOk();
            $this->receiveJson();
        }
    }

    public function testReject() {
        $drafts = [];
        $exh = [];
        $count = 5;

        for ($i = 0; $i < $count; ++$i) {
            $user = factory(User::class)->create();
            $image = factory(Image::class)->create(['user_id' => $user->id]);
            $exh[] = factory(Exhibition::class)->create([
                'thumbnail_image_id' => $image->id,
            ]);
            $drafts[] = factory(Draft::class)->create([
                'exh_id' => $exh[$i]->id,
                'user_id' => $user->id,
            ]);
        }

        foreach (['blogAdmin', 'teacher'] as $key) {
            $user = factory(User::class)->states($key)->create([]);
            $this->actingAs($user)->patch("/online/drafts/{$drafts[0]->id}/reject", []);
            $this->assertResponseOk();
            $this->receiveJson();
        }
    }

    public function testComment() {
        $drafts = [];
        $exh = [];
        $count = 5;

        for ($i = 0; $i < $count; ++$i) {
            $user = factory(User::class)->create();
            $image = factory(Image::class)->create(['user_id' => $user->id]);
            $exh[] = factory(Exhibition::class)->create([
                'thumbnail_image_id' => $image->id,
            ]);
            $drafts[] = factory(Draft::class)->create([
                'exh_id' => $exh[$i]->id,
                'user_id' => $user->id,
            ]);
        }
        $faker = Faker\Factory::create('ja_JP');

        foreach (['blogAdmin', 'teacher'] as $key) {
            $user = factory(User::class)->states($key)->create([]);
            $this->actingAs($user)->post(
                "/online/drafts/{$drafts[0]->id}/comment",
                [
                    'comment' => $faker->text(255),
                ]
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
            $user = factory(User::class)->create();
            $image = factory(Image::class)->create(['user_id' => $user->id]);
            $exh[] = factory(Exhibition::class)->create([
                'thumbnail_image_id' => $image->id,
            ]);
            $drafts[] = factory(Draft::class)->create([
                'exh_id' => $exh[$i]->id,
                'user_id' => $user->id,
            ]);
        }
        $faker = Faker\Factory::create('ja_JP');

        foreach (['blogAdmin', 'teacher'] as $key) {
            $user = factory(User::class)->states($key)->create([]);
            $this->actingAs($user)->post(
                "/online/drafts/{Str::random(8)}/comment",
                [
                    'comment' => $faker->paragraph()
                ]
            );
            $this->assertResponseStatus(404);
        }

        foreach (['blogAdmin', 'teacher'] as $key) {
            $user = factory(User::class)->states($key)->create([]);
            $this->actingAs($user)->post(
                "/online/drafts/{$drafts[0]->id}/comment"
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
            $user = factory(User::class)->create();
            $image = factory(Image::class)->create(['user_id' => $user->id]);
            $exh[] = factory(Exhibition::class)->create([
                'thumbnail_image_id' => $image->id,
            ]);
            $drafts[] = factory(Draft::class)->create([
                'exh_id' => $exh[$i]->id,
                'user_id' => $user->id,
            ]);
        }

        $user = factory(User::class)->states('blogAdmin')->create([]);
        $this->actingAs($user)->patch(
            "/online/drafts/{Str::random(8)}/publish",
            []
        );
        $this->assertResponseStatus(404);

        $this->actingAs($user)->patch(
            "/online/drafts/{$drafts[0]->id}/publish",
            []
        );
        $this->assertResponseStatus(400);

        $teacher = factory(User::class)->states('teacher')->create([]);
        $this->actingAs($teacher)->patch(
            "/online/drafts/{Str::random(8)}/publish",
            []
        );
        $this->assertResponseStatus(403);
    }

    public function testGuest() {
        $drafts = [];
        $exh = [];
        $count = 3;

        for ($i = 0; $i < $count; ++$i) {
            $user = factory(User::class)->create();
            $image = factory(Image::class)->create(['user_id' => $user->id]);
            $exh[] = factory(Exhibition::class)->create([
                'thumbnail_image_id' => $image->id,
            ]);
            $drafts[] = factory(Draft::class)->create([
                'exh_id' => $exh[$i]->id,
                'user_id' => $user->id,
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
