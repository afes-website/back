<?php
namespace Tests;

use App\Models\Article;
use App\Models\Draft;
use App\Models\Exhibition;
use App\Models\Revision;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use \Carbon\Carbon;

class ExhibitionTest extends TestCase {
    public function test_get_all() {
        $count = 5;

        for ($i = 0; $i < $count; ++$i) {
            $exhibitions[] = factory(Exhibition::class)->create([]);
        }
        $this->get('/online/exhibition');
        $this->assertResponseOk();
        $this->receiveJson();
        $this->assertCount($count, json_decode($this->response->getContent()));
    }

    public function test_show() {
        $exhibition = factory(Exhibition::class)->create([]);
        $this->get("/online/exhibition/{$exhibition->id}");
        $this->assertResponseOk();
    }

    public function test_show_not_found() {
        $this->get("/online/exhibition/{Str::random(8}");
        $this->assertResponseStatus(404);
    }
}
