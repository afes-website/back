<?php

use App\Models\Article;
use App\Models\Revision;
use Illuminate\Support\Facades\Hash;
use \Carbon\Carbon;
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
                'handle_name' => $revision->handle_name
            ]);
        }

        $this->get('/blog/articles');
        $this->assertResponseOk();
        $this->receiveJson();
        $this->assertCount($count, json_decode($this->response->getContent()));
    }

    public function test_list_filter() {
        $count = 5;

        for($i = 0; $i < $count; ++$i) {
            $article_id = Str::random(32);
            $writer_user = AuthJwt::get_token($this, ['blogWriter']);
            $revision = factory(Revision::class)->create([
                'article_id' => $article_id,
                'user_id' => $writer_user['user']->id,
                ]);
            $article = factory(Article::class)->create([
                'id' => $article_id,
                'revision_id' => $revision->id,
                'title' => $revision->title,
                'handle_name' => $revision->handle_name
            ]);
        }
        foreach([
            'id',
            'category',
            'revision_id',
            'handle_name'
            ] as $key) {
            $this->call('GET', '/blog/articles', [$key => $article->{$key}]);
            $this->assertResponseOk();

            $this->receiveJson();
            $ret_articles = json_decode($this->response->getContent());
            foreach($ret_articles as $ret_article) {
                $this->assertEquals($ret_article->{$key}, $article->{$key});
            }
        }

        $this->call('GET', '/blog/articles', ['author_id' => $article->revision->user_id]);
        $this->assertResponseOk();

        $this->receiveJson();
        $ret_articles = json_decode($this->response->getContent());
        foreach($ret_articles as $ret_article) {
            $this->assertEquals($ret_article->author->id, $article->revision->user_id);
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
            'handle_name' => $revision->handle_name
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
            'handle_name'
        ] as $key) {
            $this->assertEquals($article->{$key}, $ret->{$key});
        }
        $this->assertEquals($article->created_at->toIso8601ZuluString(), $ret->created_at);
        $this->assertEquals($article->updated_at->toIso8601ZuluString(), $ret->updated_at);

    }

    public function test_show_notfound() {
        $this->get("/blog/articles/{Str::random(8}");
        $this->assertResponseStatus(404);
        $this->receiveJson();
    }

    public function test_update() {
        $admin_user = AuthJwt::get_token($this, ['blogAdmin']);
        $article_id = Str::random(32);
        // create new first, then update
        for($i = 0; $i < 2; ++$i) {
            $revision = factory(Revision::class)->create([
                'article_id' => $article_id,
                'status' => 'accepted',
            ]);

            Carbon::setTestNow(Carbon::now()->addSeconds(10));

            $this->json('PATCH', "/blog/articles/{$article_id}",
                [
                    'revision_id' => $revision->id,
                    'category' => Str::random(32),
                ],
                $admin_user['auth_hdr']);

            $this->assertResponseOk();
            $this->receiveJson();
            $ret = json_decode($this->response->getContent());

            $this->assertEquals($revision->title, $ret->title);
            $this->assertEquals($revision->id, $ret->revision_id);
            $this->assertEquals($revision->timestamp->toIso8601ZuluString(), $ret->updated_at);

            $article = Article::find($article_id);
            $this->assertEquals($revision->title, $article->title);
            $this->assertEquals($revision->id, $article->revision_id);
            Carbon::setTestNow();
        }
    }

    public function test_update_invalid_revision() {
        $admin_user = AuthJwt::get_token($this, ['blogAdmin']);
        $revision = factory(Revision::class)->create([
            'article_id' => Str::random(32),
            'status' => 'accepted',
        ]);
        $this->json('PATCH', "/blog/articles/{Str::random(32)}",
            [
                'revision_id' => $revision->id,
                'category' => Str::random(32),
            ],
            $admin_user['auth_hdr']);

        $this->assertResponseStatus(400);
    }

    public function test_update_not_accepted() {
        $admin_user = AuthJwt::get_token($this, ['blogAdmin']);
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
            $admin_user['auth_hdr']);

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
            $admin_user['auth_hdr']);

        $this->assertResponseStatus(408);
    }

    public function test_update_not_found() {
        $admin_user = AuthJwt::get_token($this, ['blogAdmin']);
        $article_id = Str::random(32);

        $this->json('PATCH', "/blog/articles/{$article_id}",
            [
                'revision_id' => 1,
                'category' => Str::random(32),
            ],
            $admin_user['auth_hdr']);

        $this->assertResponseStatus(404);
    }

    public function test_update_guest() {
        $writer_user = AuthJwt::get_token($this, ['blogWriter']);
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
            $writer_user['auth_hdr']);
        $this->assertResponseStatus(403);

        $this->json('PATCH', "/blog/articles/{$article_id}",
            [
                'revision_id' => $revision->id,
                'category' => Str::random(32),
            ]);
        $this->assertResponseStatus(403);
    }

    public function test_update_invalid() {
        $admin_user = AuthJwt::get_token($this, ['blogAdmin']);
        $article_id = Str::random(32);

        $revision = factory(Revision::class)->create([
            'article_id' => $article_id,
            'status' => 'waiting',
        ]);


        $this->json('PATCH', "/blog/articles/{$article_id}",
            [
                'revision_id' => $revision->id,
            ],
            $admin_user['auth_hdr']);

        $this->assertResponseStatus(400);


        $this->json('PATCH', "/blog/articles/{$article_id}",
            [
                'category' => Str::random(32),
            ],
            $admin_user['auth_hdr']);

        $this->assertResponseStatus(400);


        $this->json('PATCH', "/blog/articles/{$article_id}",
            [
                'revision_id' => Str::random(8), // string
                'category' => Str::random(32),
            ],
            $admin_user['auth_hdr']);

        $this->assertResponseStatus(400);
    }

    public function test_delete() {
        $admin_user = AuthJwt::get_token($this, ['blogAdmin']);
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
            $admin_user['auth_hdr']);
        $this->assertResponseStatus(204);
        $this->assertNull(Article::find($article_id));
    }

    public function test_delete_notfound() {
        $admin_user = AuthJwt::get_token($this, ['blogAdmin']);
        $this->delete("/blog/articles/{Str::random(32)}", [],
            $admin_user['auth_hdr']);
        $this->assertResponseStatus(404);
    }

    public function test_delete_guest() {
        $writer_user = AuthJwt::get_token($this, ['blogWriter']);
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
            $writer_user['auth_hdr']);
        $this->assertResponseStatus(403);

        $this->delete("/blog/articles/{$article_id}");
        $this->assertResponseStatus(403);
    }
}
