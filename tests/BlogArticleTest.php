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
}
