<?php

use Illuminate\Support\Str;
use App\Models\Article;
use App\Models\Revision;

class OGImageTest extends TestCase {
    private function assertMimeTypeEqualsTo($mime_type) {
        $content_type_hdrs = $this->response->headers->all()['content-type'];
        $this->assertCount(1, $content_type_hdrs);
        $this->assertEquals($mime_type, $content_type_hdrs[0]);
    }

    public function test_normal() {
        $this->call('GET', '/ogimage', ['title' => Str::random(10)]);
        $this->assertResponseOk();
        $this->assertMimeTypeEqualsTo('image/png');
    }

    public function test_article() {
        $article_id = Str::random(32);
        $writer_user = WriterAuthJwt::get_token($this);
        $revision = factory(Revision::class)->create([
            'article_id' => $article_id,
            'user_id' => $writer_user['user']->id,
        ]);
        $article = factory(Article::class)->create([
            'id' => $article_id,
            'revision_id' => $revision->id,
            'title' => $revision->title,
            'category' => 'news',
        ]);
        $this->get("/ogimage/articles/$article_id");
        $this->assertResponseOk();
        $this->assertMimeTypeEqualsTo('image/png');
    }

    public function test_preview() {
        $this->call('GET', '/ogimage/preview', ['title' => Str::random(10)]);
        $this->assertResponseOk();
        $this->assertMimeTypeEqualsTo('image/png');

        $this->call('GET', '/ogimage/preview', ['title' => Str::random(10), 'author' => Str::random(10)]);
        $this->assertResponseOk();
        $this->assertMimeTypeEqualsTo('image/png');

        $this->call('GET', '/ogimage/preview', ['title' => Str::random(10), 'category' => Str::random(10)]);
        $this->assertResponseOk();
        $this->assertMimeTypeEqualsTo('image/png');

        $this->call('GET', '/ogimage/preview', ['title' => Str::random(10), 'author' => Str::random(10), 'category' => Str::random(10)]);
        $this->assertResponseOk();
        $this->assertMimeTypeEqualsTo('image/png');
    }

    public function test_normal_invalid() {
        $this->get('/ogimage');
        $this->assertResponseStatus(400);
    }

    public function test_preview_invalid() {
        $this->get('/ogimage/preview');
        $this->assertResponseStatus(400);
    }

    public function test_article_invalid() {
        $id = Str::random(40);
        $this->get("/ogimage/articles/$id");
        $this->assertResponseStatus(404);
    }
}
