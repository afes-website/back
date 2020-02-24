<?php

use App\Models\Article;
use App\Models\Revision;
use Illuminate\Support\Facades\Hash;
use \Carbon\Carbon;
use PHPUnit\Framework\Assert as PHPUnit;
use Illuminate\Support\Str;

class BlogArticleTest extends TestCase {
    public function test_get_all() {
        $revisions = [];
        $count = 5;

        for($i = 0; $i < $count; ++$i) {
            $article_id = Str::random(32);
            $revision = factory(Revision::class)->create([
                'article_id' => $article_id,
                ]);
            $article = factory(Article::class)->create([
                'id' => $article_id,
                'revision_id' => $revision->id,
                'title' => $revision->title,
            ]);
        }

        $this->get('/blog/articles');
        $this->assertResponseOk();
        $this->receiveJson();
        PHPUnit::assertCount($count, json_decode($this->response->getContent()));
    }

    public function test_list_filter() {
        $count = 5;

        for($i = 0; $i < $count; ++$i) {
            $article_id = Str::random(32);
            $revision = factory(Revision::class)->create([
                'article_id' => $article_id,
                ]);
            $article = factory(Article::class)->create([
                'id' => $article_id,
                'revision_id' => $revision->id,
                'title' => $revision->title,
            ]);
        }
        foreach([
            'id',
            'category',
            'revision_id',
            ] as $key) {
            $this->call('GET', '/blog/articles', [$key => $article->{$key}]);
            $this->assertResponseOk();

            $this->receiveJson();
            $ret_articles = json_decode($this->response->getContent());
            foreach($ret_articles as $ret_article) {
                PHPUnit::assertEquals($ret_article->{$key}, $article->{$key});
            }
        }
    }

    public function test_list_invalid_filter() {
        $this->call('GET', '/blog/articles', ['revision_id' => Str::random(8)]);
        $this->assertResponseStatus(400);
    }

    public function test_show() {
        $article_id = Str::random(32);
        $revision = factory(Revision::class)->create([
            'article_id' => $article_id,
            ]);
        $article = factory(Article::class)->create([
            'id' => $article_id,
            'revision_id' => $revision->id,
            'title' => $revision->title,
        ]);

        $this->get("/blog/articles/{$article->id}");
        $this->assertResponseOk();
        $this->receiveJson();
        $ret = json_decode($this->response->getContent());
        foreach([
            'id',
            'category',
            'title',
            'revision_id',
        ] as $key) {
            PHPUnit::assertEquals($article->{$key}, $ret->{$key});
        }
        PHPUnit::assertEquals($article->created_at->toIso8601ZuluString(), $ret->created_at);
        PHPUnit::assertEquals($article->updated_at->toIso8601ZuluString(), $ret->updated_at);

    }

    public function test_show_notfound() {
        $this->get("/blog/articles/{Str::random(8}");
        $this->assertResponseStatus(404);
        $this->receiveJson();
    }

    public function test_update() {
        $admin_user = AdminAuthJwt::get_token($this);
        $article_id = Str::random(32);
        // create new first, then update
        for($i = 0; $i < 2; ++$i) {
            $revision = factory(Revision::class)->create([
                'article_id' => $article_id,
                'status' => 'accepted',
            ]);

            $this->json('PATCH', "/blog/articles/{$article_id}",
                [
                    'revision_id' => $revision->id,
                    'category' => Str::random(32),
                ],
                [
                    'X-ADMIN-TOKEN' => $admin_user['token']
                ]);

            $this->assertResponseOk();
            $this->receiveJson();
            $ret = json_decode($this->response->getContent());

            PHPUnit::assertEquals($revision->title, $ret->title);
            PHPUnit::assertEquals($revision->id, $ret->revision_id);

            $article = Article::find($article_id);
            PHPUnit::assertEquals($revision->title, $article->title);
            PHPUnit::assertEquals($revision->id, $article->revision_id);

        }
    }

    public function test_update_invalid_revision() {
        $admin_user = AdminAuthJwt::get_token($this);
        $revision = factory(Revision::class)->create([
            'article_id' => Str::random(32),
            'status' => 'accepted',
        ]);
        $this->json('PATCH', "/blog/articles/{Str::random(32)}",
            [
                'revision_id' => $revision->id,
                'category' => Str::random(32),
            ],
            [
                'X-ADMIN-TOKEN' => $admin_user['token']
            ]);

        $this->assertResponseStatus(400);
    }

    public function test_update_not_accepted() {
        $admin_user = AdminAuthJwt::get_token($this);
        $article_id = Str::random(32);

        $revision = factory(Revision::class)->create([
            'article_id' => $article_id,
            'status' => 'waiting',
        ]);

        $this->json('PATCH', "/blog/articles/{$article_id}",
            [
                'revision_id' => $revision->id,
                'category' => Str::random(32),
            ],
            [
                'X-ADMIN-TOKEN' => $admin_user['token']
            ]);

        $this->assertResponseStatus(408);

        $revision = factory(Revision::class)->create([
            'article_id' => $article_id,
            'status' => 'rejected',
        ]);

        $this->json('PATCH', "/blog/articles/{$article_id}",
            [
                'revision_id' => $revision->id,
                'category' => Str::random(32),
            ],
            [
                'X-ADMIN-TOKEN' => $admin_user['token']
            ]);

        $this->assertResponseStatus(408);
    }

    public function test_update_not_found() {
        $admin_user = AdminAuthJwt::get_token($this);
        $article_id = Str::random(32);

        $this->json('PATCH', "/blog/articles/{$article_id}",
            [
                'revision_id' => 1,
                'category' => Str::random(32),
            ],
            [
                'X-ADMIN-TOKEN' => $admin_user['token']
            ]);

        $this->assertResponseStatus(404);
    }

    public function test_update_guest() {
        $writer_user = WriterAuthJwt::get_token($this);
        $article_id = Str::random(32);

        $revision = factory(Revision::class)->create([
            'article_id' => $article_id,
            'status' => 'waiting',
        ]);

        $this->json('PATCH', "/blog/articles/{$article_id}",
            [
                'revision_id' => $revision->id,
                'category' => Str::random(32),
            ],
            [
                'X-ADMIN-TOKEN' => $writer_user['token']
            ]);
        $this->assertResponseStatus(401);

        $this->json('PATCH', "/blog/articles/{$article_id}",
            [
                'revision_id' => $revision->id,
                'category' => Str::random(32),
            ]);
        $this->assertResponseStatus(401);
    }

    public function test_update_invalid() {
        $admin_user = AdminAuthJwt::get_token($this);
        $article_id = Str::random(32);

        $revision = factory(Revision::class)->create([
            'article_id' => $article_id,
            'status' => 'waiting',
        ]);


        $this->json('PATCH', "/blog/articles/{$article_id}",
            [
                'revision_id' => $revision->id,
            ],
            [
                'X-ADMIN-TOKEN' => $admin_user['token']
            ]);

        $this->assertResponseStatus(400);


        $this->json('PATCH', "/blog/articles/{$article_id}",
            [
                'category' => Str::random(32),
            ],
            [
                'X-ADMIN-TOKEN' => $admin_user['token']
            ]);

        $this->assertResponseStatus(400);


        $this->json('PATCH', "/blog/articles/{$article_id}",
            [
                'revision_id' => Str::random(8), // string
                'category' => Str::random(32),
            ],
            [
                'X-ADMIN-TOKEN' => $admin_user['token']
            ]);

        $this->assertResponseStatus(400);
    }

    public function test_delete() {
        $admin_user = AdminAuthJwt::get_token($this);
        $article_id = Str::random(32);
        $revision = factory(Revision::class)->create([
            'article_id' => $article_id,
            ]);
        $article = factory(Article::class)->create([
            'id' => $article_id,
            'revision_id' => $revision->id,
            'title' => $revision->title,
        ]);
        $this->delete("/blog/articles/{$article_id}", [],
            [
                'X-ADMIN-TOKEN' => $admin_user['token'],
            ]);
        $this->assertResponseStatus(204);
        PHPUnit::assertNull(Article::find($article_id));
    }

    public function test_delete_notfound() {
        $admin_user = AdminAuthJwt::get_token($this);
        $this->delete("/blog/articles/{Str::random(32)}", [],
            [
                'X-ADMIN-TOKEN' => $admin_user['token'],
            ]);
        $this->assertResponseStatus(404);
    }

    public function test_delete_guest() {
        $writer_user = WriterAuthJwt::get_token($this);
        $article_id = Str::random(32);
        $revision = factory(Revision::class)->create([
            'article_id' => $article_id,
            ]);
        $article = factory(Article::class)->create([
            'id' => $article_id,
            'revision_id' => $revision->id,
            'title' => $revision->title,
        ]);

        $this->delete("/blog/articles/{$article_id}", [],
            [
                'X-BLOG-WRITER-TOKEN' => $writer_user['token']
            ]);
        $this->assertResponseStatus(401);

        $this->delete("/blog/articles/{$article_id}");
        $this->assertResponseStatus(401);
    }
}
