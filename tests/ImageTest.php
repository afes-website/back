<?php
namespace Tests;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ImageTest extends TestCase {
    public function testUpload() {
        $writer_user = factory(User::class, 'blogWriter')->create();
        $this->actingAs($writer_user)->call(
            'POST',
            '/images',
            [],
            [],
            [
                'content' => UploadedFile::fake()->image('hoge.jpg')
            ]
        );
        $this->assertResponseStatus(201);

        $this->seeJsonStructure(['id']);
    }

    public function testUploadWithoutLogin() {
        $this->call(
            'POST',
            '/images',
            [],
            [],
            [
                'content' => UploadedFile::fake()->image('hoge.jpg')
            ]
        );
        $this->assertResponseStatus(401);
    }

    public function testUploadNonImage() {
        $writer_user = factory(User::class, 'blogWriter')->create();
        $this->actingAs($writer_user)->call(
            'POST',
            '/images',
            [],
            [],
            [
                'content' => UploadedFile::fake()->create('hoge.txt')
            ]
        );
        $this->assertResponseStatus(400);
    }

    public function testDownload() {
        $height = $width = 10;
        $file = UploadedFile::fake()->image('hoge.png', $width, $height);
        $writer_user = factory(User::class, 'blogWriter')->create();
        $this->actingAs($writer_user)->call(
            'POST',
            '/images',
            [],
            [],
            [
                'content' => $file
            ]
        );

        $id = $this->response->original['id'];

        $this->get("/images/{$id}");
        $this->assertResponseOk();
        // $this->assertEquals($this->response->getContent(), $file->get()); // because of re-encoding
        $img = \Intervention\Image\Facades\Image::make($this->response->getContent());
        $this->assertEquals($img->width(), $width);
        $this->assertEquals($img->height(), $height);
    }

    public function testDownloadLarge() {
        $height = random_int(1, 600);
        $width = random_int(1081, 2000); // > 1080
        $file = UploadedFile::fake()->image('hoge.png', $width, $height);
        $writer_user = factory(User::class, 'blogWriter')->create();
        $this->actingAs($writer_user)->call(
            'POST',
            '/images',
            [],
            [],
            [
                'content' => $file
            ]
        );

        $id = $this->response->original['id'];

        $this->get("/images/{$id}");
        $this->assertResponseOk();
        $img = \Intervention\Image\Facades\Image::make($this->response->getContent());
        $this->assertEquals($img->width(), 1080);

        $height = random_int(601, 2000); // > 600
        $width = random_int(1, 1080);
        $file = UploadedFile::fake()->image('hoge.png', $width, $height);
        $writer_user = factory(User::class, 'blogWriter')->create();
        $this->call(
            'POST',
            '/images',
            [],
            [],
            [
                'content' => $file
            ]
        );

        $id = $this->response->original['id'];

        $this->get("/images/{$id}");
        $this->assertResponseOk();
        $img = \Intervention\Image\Facades\Image::make($this->response->getContent());
        $this->assertEquals($img->height(), 600);
    }

    public function testDownloadResize() {
        $get_height = random_int(1, 2000);
        $get_width = random_int(1, 2000);

        $file = UploadedFile::fake()->image('hoge.png');
        $writer_user = factory(User::class, 'blogWriter')->create();
        $this->actingAs($writer_user)->call(
            'POST',
            '/images',
            [],
            [],
            [
                'content' => $file
            ]
        );

        $id = $this->response->original['id'];

        $this->get("/images/{$id}?w={$get_width}&h={$get_height}");
        $this->assertResponseOk();
        $img = \Intervention\Image\Facades\Image::make($this->response->getContent());
        $this->assertEquals($img->height(), $get_height);
        $this->assertEquals($img->width(), $get_width);
    }

    public function testDownloadOrig() {
        $height = random_int(601, 2000); // > 600
        $width = random_int(1081, 2000); // > 1080
        $file = UploadedFile::fake()->image('hoge.png', $width, $height);
        $writer_user = factory(User::class, 'blogWriter')->create();
        $this->actingAs($writer_user)->call(
            'POST',
            '/images',
            [],
            [],
            [
                'content' => $file
            ]
        );

        $id = $this->response->original['id'];

        $this->get("/images/{$id}?orig");
        $this->assertResponseOk();
        $img = \Intervention\Image\Facades\Image::make($this->response->getContent());
        $this->assertEquals($img->height(), $height);
        $this->assertEquals($img->width(), $width);
    }

    public function testDownloadNotFound() {
        $id = Str::random(40);

        $this->get("/images/{$id}");
        $this->assertResponseStatus(404);
    }
}
