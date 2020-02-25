<?php

use App\Models\AdminUser;
use App\Models\WriterUser;
use Illuminate\Support\Facades\Hash;
use \Carbon\Carbon;
use \Illuminate\Support\Str;

class WriterAuthJwt extends TestCase {
    public static function get_token(TestCase $tc) {
        $password = Str::random(16);
        $user = factory(WriterUser::class)->create([
            'password'=>Hash::make($password)
        ]);
        $id = $user->id;
        $response = $tc->post('/writer/login',
            ['id'=>$id, 'password'=>$password]);
        $response->assertResponseOk();
        $response->seeJsonStructure(['token']);

        $jwc_token = json_decode($response->response->getContent())->token;

        return [
            'user' => $user,
            'password' => $password,
            'token' => $jwc_token
        ];
    }
    /**
     * /writer/login allow only post
     * @return void
     */
    public function test_login_get_not_allowed() {
        $response = $this->get('/writer/login');
        $response->assertResponseStatus(405);
    }

    /**
     * /writer/user allow only get
     * @return void
     */
    public function test_user_post_not_allowed() {
        $response = $this->post('/writer/user');
        $response->assertResponseStatus(405);
    }

    /**
     * logging in with not existing user will be failed
     *
     * @return void
     */
    public function test_user_not_found() {
        $response = $this->post('/writer/login',
            ['id'=>'not_existing_user', 'password'=>'hogehoge']);
        $response->assertResponseStatus(401);
    }

    /**
     * logging in with wring password will be failed
     *
     * @return void
     */
    public function test_password_wrong() {
        factory(WriterUser::class)->create([
            'id'=>'writer',
            'password'=>Hash::make('password')
        ]);
        $response = $this->post('/writer/login',
            ['id'=>'writer', 'password'=>'wrong_password']);
        $response->assertResponseStatus(401);
    }

    /**
     * login returns jwc token
     *
     * @return void
     */
    public function test_login_successful() {
        // login and get token
        $password = 'password';
        $user = factory(WriterUser::class)->create([
            'password'=>Hash::make($password)
        ]);
        $id = $user->id;
        $name = $user->name;

        $response = $this->post('/writer/login',
            ['id'=>$id, 'password'=>$password]);
        $response->assertResponseOk();
        $response->seeJsonStructure(['token']);

        $jwc_token = json_decode($response->response->getContent())->token;

        $response = $this->get('/writer/user', ['X-BLOG-WRITER-TOKEN'=>$jwc_token]);
        $response->assertResponseOk();
        $response->seeJsonEquals([
            'id'=>$id,
            'name'=>$name
        ]);
    }

    /**
     * user info denies access without token
     * @return void
     */
    public function test_no_token() {
        $response = $this->get('/writer/user');
        $response->assertResponseStatus(401);

        $response = $this->get('/writer/user', ['X-BLOG-WRITER-TOKEN'=>'invalid_token']);
        $response->assertResponseStatus(401);
    }

    /**
     * user info denies access with expired token
     * @return void
     */
    public function test_expired_token() {
        // login and get token
        $password = 'password';
        $user = factory(WriterUser::class)->create([
            'password'=>Hash::make($password)
        ]);
        $id = $user->id;
        $name = $user->name;

        $response = $this->post('/writer/login',
            ['id'=>$id, 'password'=>$password]);
        $response->assertResponseOk();
        $response->seeJsonStructure(['token']);

        $jwc_token = json_decode($response->response->getContent())->token;

        Carbon::setTestNow(Carbon::now()->addSeconds(env('JWT_EXPIRE')+1));
        // now token must be expired

        $response = $this->get('/writer/user', ['X-BLOG-WRITER-TOKEN'=>$jwc_token]);
        $response->assertResponseStatus(401);
    }

    /**
     * getting admin user info with writer user will be rejected
     * @return void
     */
    public function test_admin_user() {
        $password = 'password';
        $user = factory(WriterUser::class)->create([
            'password'=>Hash::make($password)
        ]);
        $id = $user->id;

        $admin_password = 'different_password';
        $user = factory(AdminUser::class)->create([
            'id'=>$id, // same as WriterUser's one
            'password'=>Hash::make($admin_password)
        ]);

        $response = $this->post('/writer/login',
            ['id'=>$id, 'password'=>$password]);
        $response->assertResponseOk();
        $response->seeJsonStructure(['token']);

        $jwc_token = json_decode($response->response->getContent())->token;

        $response = $this->get('/writer/user', ['X-BLOG-WRITER-TOKEN'=>$jwc_token]);
        $response->assertResponseOk();

        $response = $this->get('/admin/user', ['X-BLOG-WRITER-TOKEN'=>$jwc_token]);
        $response->assertResponseStatus(401);

        $response = $this->get('/admin/user', ['X-ADMIN-TOKEN'=>$jwc_token]);
        $response->assertResponseStatus(401);
    }
}
