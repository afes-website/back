<?php

use App\Models\AdminUser;
use Illuminate\Support\Facades\Hash;
use \Carbon\Carbon;
use \Illuminate\Support\Str;

class AdminAuthJwt extends TestCase {
    public static function get_token(TestCase $tc) {
        $password = Str::random(16);
        $user = factory(AdminUser::class)->create([
            'password'=>Hash::make($password)
        ]);
        $id = $user->id;
        $response = $tc->post('/admin/login',
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
     * /admin/login allow only post
     * @return void
     */
    public function test_login_get_not_allowed() {
        $response = $this->get('/admin/login');
        $response->assertResponseStatus(405);
    }

    /**
     * /admin/user allow only get
     * @return void
     */
    public function test_user_post_not_allowed() {
        $response = $this->post('/admin/user');
        $response->assertResponseStatus(405);
    }

    /**
     * logging in with not existing user will be failed
     *
     * @return void
     */
    public function test_user_not_found() {
        $response = $this->post('/admin/login',
            ['id'=>'not_existing_user', 'password'=>'hogehoge']);
        $response->assertResponseStatus(401);
    }

    /**
     * logging in with wring password will be failed
     *
     * @return void
     */
    public function test_password_wrong() {
        factory(AdminUser::class)->create([
            'id'=>'admin',
            'password'=>Hash::make('password')
        ]);
        $response = $this->post('/admin/login',
            ['id'=>'admin', 'password'=>'wrong_password']);
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
        $user = factory(AdminUser::class)->create([
            'password'=>Hash::make($password)
        ]);
        $id = $user->id;
        $name = $user->name;

        $response = $this->post('/admin/login',
            ['id'=>$id, 'password'=>$password]);
        $response->assertResponseOk();
        $response->seeJsonStructure(['token']);

        $jwc_token = json_decode($response->response->getContent())->token;

        $response = $this->get('/admin/user', ['X-ADMIN-TOKEN'=>$jwc_token]);
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
        $response = $this->get('/admin/user');
        $response->assertResponseStatus(401);

        $response = $this->get('/admin/user', ['X-ADMIN-TOKEN'=>'invalid_token']);
        $response->assertResponseStatus(401);
    }

    /**
     * user info denies access with expired token
     * @return void
     */
    public function test_expired_token() {
        // login and get token
        $password = 'password';
        $user = factory(AdminUser::class)->create([
            'password'=>Hash::make($password)
        ]);
        $id = $user->id;
        $name = $user->name;

        $response = $this->post('/admin/login',
            ['id'=>$id, 'password'=>$password]);
        $response->assertResponseOk();
        $response->seeJsonStructure(['token']);

        $jwc_token = json_decode($response->response->getContent())->token;

        Carbon::setTestNow(Carbon::now()->addSeconds(env('JWT_EXPIRE')+1));
        // now token must be expired

        $response = $this->get('/admin/user', ['X-ADMIN-TOKEN'=>$jwc_token]);
        $response->assertResponseStatus(401);
    }

    /**
     * change password without login must be failed
     * @return void
     */
    public function test_change_password_anonymously() {
        $new_password = Str::random(16);
        $response = $this->post('/admin/change_password',
            ['password'=>$new_password]);
        $response->assertResponseStatus(401);
    }

    /**
     * password less than 8 chars must be rejected
     * @return void
     */
    public function test_weak_new_password() {
        // create user
        $old_password = Str::random(16); // initial does not matter
        $new_weak_password = Str::random(7); // < 8
        $new_strong_password = Str::random(8); // >= 8

        $user = factory(AdminUser::class)->create([
            'password'=>Hash::make($old_password)
        ]);
        $id = $user->id;

        // login first
        $response = $this->post('/admin/login',
            ['id'=>$id, 'password'=>$old_password]);
        $response->assertResponseOk();
        $response->seeJsonStructure(['token']);

        $jwc_token = json_decode($response->response->getContent())->token;

        // weak password must be rejected
        $response = $this->post('/admin/change_password',
            ['password'=>$new_weak_password],
            ['X-ADMIN-TOKEN'=>$jwc_token]);
        $response->assertResponseStatus(400);

        // strong password must be accepted
        $response = $this->post('/admin/change_password',
            ['password'=>$new_strong_password],
            ['X-ADMIN-TOKEN'=>$jwc_token]);
        $response->assertResponseStatus(204);
    }

    /**
     * changing password
     * @return void
     */
    public function test_change_password() {
            // create user
            $old_password = Str::random(16);
            $new_password = Str::random(16);
            $user = factory(AdminUser::class)->create([
                'password'=>Hash::make($old_password)
            ]);
            $id = $user->id;

            // login first
            $response = $this->post('/admin/login',
                ['id'=>$id, 'password'=>$old_password]);
            $response->assertResponseOk();
            $response->seeJsonStructure(['token']);

            $jwc_token = json_decode($response->response->getContent())->token;

            // change password
            $response = $this->post('/admin/change_password',
                ['password' => $new_password],
                ['X-ADMIN-TOKEN'=>$jwc_token]);
            $response->assertResponseStatus(204);

            // old password is no longer valid
            $response = $this->post('/admin/login',
            ['id'=>$id, 'password'=>$old_password]);
            $response->assertResponseStatus(401);

            // use new password instead old one
            $response = $this->post('/admin/login',
            ['id'=>$id, 'password'=>$new_password]);
            $response->assertResponseOk();
    }
}
