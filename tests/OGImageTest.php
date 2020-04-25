<?php

use Illuminate\Support\Str;
use App\Models\Article;
use App\Models\Revision;

class OGImageTest extends TestCase {
    public function test_ok() {
        $title = Str::random(10);
        $this->call('GET', '/ogimage', ['title' => $title]);
        $this->assertResponseOk();

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

        $this->call('GET', '/ogimage/preview', ['title' => $title]);
        $this->assertResponseOk();
    }
    public function test_preview_ok() {
        $this->get('/ogimage/preview');
        $this->assertResponseStatus(400);
    }
    public function test_no_param() {
        $this->get('/ogimage');
        $this->assertResponseStatus(400);

        $this->get('/ogimage/preview');
        $this->assertResponseStatus(400);
    }
    public function test_articles_invalid_id() {
        $id = Str::random(40);
        $this->get("/ogimage/articles/$id");
        $this->assertResponseStatus(404);
    }
}
