<?php
namespace Tests;

use App\Models\Article;
use App\Models\Draft;
use App\Models\Exhibition;
use App\Models\Revision;
use App\Models\User;
use App\Models\Image;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use \Carbon\Carbon;

class ExhibitionTest extends TestCase {
    public function testGetAll() {
        $count = 5;

        for ($i = 0; $i < $count; ++$i) {
            $user = factory(User::class)->states('blogWriter')->create();
            $image = factory(Image::class)->create(['user_id' => $user->id]);
            $exhibitions[] = factory(Exhibition::class)->create([
                'thumbnail_image_id' => $image->id,
            ]);
        }
        $this->get('/online/exhibition');
        $this->assertResponseOk();
        $this->receiveJson();
        $this->assertCount($count, json_decode($this->response->getContent()));
    }

    public function testShow() {
        $user = factory(User::class)->states('blogWriter')->create();
        $image = factory(Image::class)->create(['user_id' => $user->id]);
        $exhibition = factory(Exhibition::class)->create([
            'thumbnail_image_id' => $image->id,
        ]);
        $this->get("/online/exhibition/{$exhibition->id}");
        $this->assertResponseOk();
    }

    public function testShowNotFound() {
        $this->get("/online/exhibition/{Str::random(8}");
        $this->assertResponseStatus(404);
    }
}
