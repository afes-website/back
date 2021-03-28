<?php
namespace Tests;

use App\Models\Article;
use App\Models\Revision;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use \Carbon\Carbon;
use Illuminate\Support\Str;

class BlogArticleTest extends TestCase {
    public function testGetAll() {
        $revisions = [];
        $count = 5;

        for ($i = 0; $i < $count; ++$i) {
            $article_id = Str::random(32);
            $user = factory(User::class)->states('blogWriter')->create();
            $revision = factory(Revision::class)->create([
                'article_id' => $article_id,
                'user_id' => $user->id,
            ]);
            $article = factory(Article::class)->create([
                'id' => $article_id,
                'revision_id' => $revision->id,
                'title' => $revision->title,
                'handle_name' => $revision->handle_name
            ]);
        }

        $this->get('/blog/articles');
        $this->assertJson($this->response->getContent());
        $this->assertCount($count, json_decode($this->response->getContent()));
    }

    public function testListFilter() {
        $count = 5;

        for ($i = 0; $i < $count; ++$i) {
            $article_id = Str::random(32);
            $writer_user = factory(User::class)->states('blogWriter')->create();
            $revision = factory(Revision::class)->create([
                'article_id' => $article_id,
                'user_id' => $writer_user->id,
            ]);
            $article = factory(Article::class)->create([
                'id' => $article_id,
                'revision_id' => $revision->id,
                'title' => $revision->title,
                'handle_name' => $revision->handle_name
            ]);
        }
        foreach ([
            'id',
            'category',
            'revision_id',
            'handle_name',
        ] as $key) {
            $this->call('GET', '/blog/articles', [$key => $article->{$key}]);
            $this->assertResponseOk();

            $this->assertJson($this->response->getContent());
            $ret_articles = json_decode($this->response->getContent());
            foreach ($ret_articles as $ret_article) {
                $this->assertEquals($ret_article->{$key}, $article->{$key});
            }
        }

        $this->call('GET', '/blog/articles', ['author_id' => $article->revision->user_id]);
        $this->assertResponseOk();

        $this->assertJson($this->response->getContent());
        $ret_articles = json_decode($this->response->getContent());
        foreach ($ret_articles as $ret_article) {
            $this->assertEquals($ret_article->author->id, $article->revision->user_id);
        }
    }

    public function testListInvalidFilter() {
        $this->call('GET', '/blog/articles', ['revision_id' => Str::random(8)]);
        $this->assertResponseStatus(400);
    }

    public function testShow() {
        $article_id = Str::random(32);
        $user = factory(User::class)->states('blogWriter')->create();
        $revision = factory(Revision::class)->create([
            'article_id' => $article_id,
            'user_id' => $user->id,
        ]);
        $article = factory(Article::class)->create([
            'id' => $article_id,
            'revision_id' => $revision->id,
            'title' => $revision->title,
            'handle_name' => $revision->handle_name
        ]);

        $this->get("/blog/articles/{$article->id}");
        $this->assertResponseOk();
        $this->assertJson($this->response->getContent());
        $ret = json_decode($this->response->getContent());
        foreach ([
            'id',
            'category',
            'title',
            'revision_id',
            'handle_name',
        ] as $key) {
            $this->assertEquals($article->{$key}, $ret->{$key});
        }
        $this->assertEquals($article->created_at->toIso8601ZuluString(), $ret->created_at);
        $this->assertEquals($article->updated_at->toIso8601ZuluString(), $ret->updated_at);
    }

    public function testShowNotfound() {
        $this->get("/blog/articles/{Str::random(8}");
        $this->assertResponseStatus(404);
        $this->assertJson($this->response->getContent());
    }

    public function testUpdate() {
        $admin_user = factory(User::class)->states('blogAdmin')->create();
        $article_id = Str::random(32);
        // create new first, then update
        for ($i = 0; $i < 2; ++$i) {
            $user = factory(User::class)->states('blogWriter')->create();
            $revision = factory(Revision::class)->create([
                'article_id' => $article_id,
                'status' => 'accepted',
                'user_id' => $user->id,
            ]);

            $this->actingAs($admin_user)->patch(
                "/blog/articles/{$article_id}",
                [
                    'revision_id' => $revision->id,
                    'category' => Str::random(32),
                ]
            );

            $this->assertResponseOk();
            $this->assertJson($this->response->getContent());
            $ret = json_decode($this->response->getContent());

            $this->assertEquals($revision->title, $ret->title);
            $this->assertEquals($revision->id, $ret->revision_id);

            $article = Article::find($article_id);
            $this->assertEquals($revision->title, $article->title);
            $this->assertEquals($revision->id, $article->revision_id);
        }
    }

    public function testUpdateInvalidRevision() {
        $admin_user = factory(User::class)->states('blogAdmin')->create();
        $user = factory(User::class)->states('blogWriter')->create();
        $revision = factory(Revision::class)->create([
            'article_id' => Str::random(32),
            'status' => 'accepted',
            'user_id' => $user->id,
        ]);
        $this->actingAs($admin_user)->json(
            'PATCH',
            "/blog/articles/{Str::random(32)}",
            [
                'revision_id' => $revision->id,
                'category' => Str::random(32),
            ]
        );

        $this->assertResponseStatus(400);
    }

    public function testUpdateNotAccepted() {
        $admin_user = factory(User::class)->states('blogAdmin')->create();
        $article_id = Str::random(32);

        $user = factory(User::class)->states('blogWriter')->create();
        $revision = factory(Revision::class)->create([
            'article_id' => $article_id,
            'status' => 'waiting',
            'user_id' => $user->id,
        ]);

        $this->actingAs($admin_user)->json(
            'PATCH',
            "/blog/articles/{$article_id}",
            [
                'revision_id' => $revision->id,
                'category' => Str::random(32),
            ]
        );

        $this->assertResponseStatus(408);

        $revision = factory(Revision::class)->create([
            'article_id' => $article_id,
            'status' => 'rejected',
            'user_id' => $user->id,
        ]);

        $this->actingAs($admin_user)->json(
            'PATCH',
            "/blog/articles/{$article_id}",
            [
                'revision_id' => $revision->id,
                'category' => Str::random(32),
            ]
        );

        $this->assertResponseStatus(408);
    }

    public function testUpdateNotFound() {
        $admin_user = factory(User::class)->states('blogAdmin')->create();
        $article_id = Str::random(32);

        $this->actingAs($admin_user)->json(
            'PATCH',
            "/blog/articles/{$article_id}",
            [
                'revision_id' => 1,
                'category' => Str::random(32),
            ]
        );

        $this->assertResponseStatus(404);
    }

    public function testUpdateGuest() {
        $writer_user = factory(User::class)->states('blogWriter')->create();
        $article_id = Str::random(32);

        $oth_user = factory(User::class)->states('blogWriter')->create();
        $revision = factory(Revision::class)->create([
            'article_id' => $article_id,
            'status' => 'waiting',
            'user_id' => $oth_user->id,
        ]);

        $this->actingAs($writer_user)->json(
            'PATCH',
            "/blog/articles/{$article_id}",
            [
                'revision_id' => $revision->id,
                'category' => Str::random(32),
            ]
        );
        $this->assertResponseStatus(403);

        $this->json(
            'PATCH',
            "/blog/articles/{$article_id}",
            [
                'revision_id' => $revision->id,
                'category' => Str::random(32),
            ]
        );
        $this->assertResponseStatus(403);
    }

    public function testUpdateInvalid() {
        $admin_user = factory(User::class)->states('blogAdmin')->create();
        $article_id = Str::random(32);

        $user = factory(User::class)->states('blogWriter')->create();
        $revision = factory(Revision::class)->create([
            'article_id' => $article_id,
            'status' => 'waiting',
            'user_id' => $user->id,
        ]);


        $this->actingAs($admin_user)->json(
            'PATCH',
            "/blog/articles/{$article_id}",
            [
                'revision_id' => $revision->id,
            ]
        );

        $this->assertResponseStatus(400);


        $this->actingAs($admin_user)->json(
            'PATCH',
            "/blog/articles/{$article_id}",
            [
                'category' => Str::random(32),
            ]
        );

        $this->assertResponseStatus(400);


        $this->actingAs($admin_user)->json(
            'PATCH',
            "/blog/articles/{$article_id}",
            [
                'revision_id' => Str::random(8), // string
                'category' => Str::random(32),
            ]
        );

        $this->assertResponseStatus(400);
    }

    public function testDelete() {
        $admin_user = factory(User::class)->states('blogAdmin')->create();
        $article_id = Str::random(32);
        $user = factory(User::class)->states('blogWriter')->create();
        $revision = factory(Revision::class)->create([
            'article_id' => $article_id,
            'user_id' => $user->id,
        ]);
        $article = factory(Article::class)->create([
            'id' => $article_id,
            'revision_id' => $revision->id,
            'title' => $revision->title,
        ]);
        $this->actingAs($admin_user)->delete(
            "/blog/articles/{$article_id}",
            []
        );
        $this->assertResponseStatus(204);
        $this->assertNull(Article::find($article_id));
    }

    public function testDeleteNotfound() {
        $admin_user = factory(User::class)->states('blogAdmin')->create();
        $this->actingAs($admin_user)->delete(
            "/blog/articles/{Str::random(32)}",
            []
        );
        $this->assertResponseStatus(404);
    }

    public function testDeleteGuest() {
        $writer_user = factory(User::class)->states('blogWriter')->create();
        $article_id = Str::random(32);
        $oth_user = factory(User::class)->states('blogWriter')->create();
        $revision = factory(Revision::class)->create([
            'article_id' => $article_id,
            'user_id' => $oth_user->id,
        ]);
        $article = factory(Article::class)->create([
            'id' => $article_id,
            'revision_id' => $revision->id,
            'title' => $revision->title,
        ]);

        $this->actingAs($writer_user)->delete(
            "/blog/articles/{$article_id}",
            []
        );
        $this->assertResponseStatus(403);

        $this->delete("/blog/articles/{$article_id}");
        $this->assertResponseStatus(403);
    }
}
