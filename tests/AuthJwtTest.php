<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use \Carbon\Carbon;
use \Illuminate\Support\Str;

class AuthJwt extends TestCase {
    public static function get_token(TestCase $tc, $perms = []) {
        $password = Str::random(16);

        $data = [
            'password'=>Hash::make($password)
        ];
        foreach ($perms as $val) $data['perm_' . $val] = true;
        $user = factory(User::class)->create($data);
        $id = $user->id;
        $response = $tc->json('POST', '/auth/login',
            ['id'=>$id, 'password'=>$password]);
        $response->assertResponseOk();
        $response->seeJsonStructure(['token']);

        $jwc_token = json_decode($response->response->getContent())->token;

        return [
            'user' => $user,
            'password' => $password,
            'auth_hdr' => ['Authorization' => "bearer {$jwc_token}"],
        ];
    }
    /**
     * /auth/login allow only post
     * @return void
     */
    public function test_login_get_not_allowed() {
        $response = $this->get('/auth/login');
        $response->assertResponseStatus(405);
    }

    /**
     * /auth/user allow only get
     * @return void
     */
    public function test_user_post_not_allowed() {
        $response = $this->json('POST', '/auth/user');
        $response->assertResponseStatus(405);
    }

    /**
     * logging in with not existing user will be failed
     *
     * @return void
     */
    public function test_user_not_found() {
        $response = $this->json('POST', '/auth/login',[
            'id'=>Str::random(16),
            'password'=>Str::random(16)
        ]);
        $response->assertResponseStatus(401);
    }

    /**
     * logging in with wring password will be failed
     *
     * @return void
     */
    public function test_password_wrong() {
        $user = $this->get_token($this);
        $response = $this->json('POST', '/auth/login',[
            'id'=>$user['user']->id,
            'password'=>Str::random(16)
        ]);
        $response->assertResponseStatus(401);
    }

    /**
     * login returns jwc token
     *
     * @return void
     */
    public function test_login_successful() {
        // login and get token
        $user = $this->get_token($this);

        $response = $this->json('POST', '/auth/login',[
            'id'=>$user['user']->id,
            'password'=>$user['password']
        ]);
        $response->assertResponseOk();
        $response->seeJsonStructure(['token']);

        $jwc_token = json_decode($response->response->getContent())->token;

        $response = $this->get('/auth/user', ['Authorization'=>'bearer '.$jwc_token]);
        $response->assertResponseOk();
        $response->seeJson([
            'id'=>$user['user']->id,
            'name'=>$user['user']->name
        ]);
    }

    /**
     * user info permission bits
     * @return void
     */
    public function test_permission_obj() {
        $perms = [];
        foreach ([
                'admin',
                'blogAdmin',
                'blogWriter',
                'exhibition',
                'general',
                'reservation'] as $name) {
            if (rand(0, 1) === 1) $perms[] = $name;
        }

        $user = $this->get_token($this, $perms);
        $response = $this->get('/auth/user', $user['auth_hdr']);
        $response->assertResponseOk();
        $response->seeJsonEquals([
            'id' => $user['user']->id,
            'name' => $user['user']->name,
            'permissions' => [
                'admin' => $user['user']->perm_admin,
                'blogAdmin' => $user['user']->perm_blogAdmin,
                'blogWriter' => $user['user']->perm_blogWriter,
                'exhibition' => $user['user']->perm_exhibition,
                'general' => $user['user']->perm_general,
                'reservation' => $user['user']->perm_reservation,
                'teacher' => $user['user']->perm_teacher,
            ],
        ]);
    }

    /**
     * user info denies access without token
     * @return void
     */
    public function test_no_token() {
        $response = $this->get('/auth/user');
        $response->assertResponseStatus(401);

        $response = $this->get('/auth/user', ['Authorization'=>'bearer invalid_token']);
        $response->assertResponseStatus(401);
    }

    /**
     * user info denies access with expired token
     * @return void
     */
    public function test_expired_token() {
        // login and get token
        $user = $this->get_token($this);

        Carbon::setTestNow(Carbon::now()->addSeconds(env('JWT_EXPIRE')+1));
        // now token must be expired

        $response = $this->get('/auth/user', $user['auth_hdr']);
        $response->assertResponseStatus(401);
    }

    /**
     * change password without login must be failed
     * @return void
     */
    public function test_change_password_anonymously() {
        $new_password = Str::random(16);
        $response = $this->json('POST', '/auth/change_password',
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

        $user = factory(User::class)->create([
            'password'=>Hash::make($old_password)
        ]);
        $id = $user->id;

        // login first
        $response = $this->json('POST', '/auth/login',
            ['id'=>$id, 'password'=>$old_password]);
        $response->assertResponseOk();
        $response->seeJsonStructure(['token']);

        $jwc_token = json_decode($response->response->getContent())->token;

        // weak password must be rejected
        $response = $this->json('POST', '/auth/change_password',
            ['password'=>$new_weak_password],
            ['Authorization'=>'bearer '.$jwc_token]);
        $response->assertResponseStatus(400);

        // strong password must be accepted
        $response = $this->json('POST', '/auth/change_password',
            ['password'=>$new_strong_password],
            ['Authorization'=>'bearer '.$jwc_token]);
        $response->assertResponseStatus(204);
    }

    /**
     * changing password
     * @return void
     */
    public function test_change_password() {
        // create user
        $new_password = Str::random(16);

        $user = $this->get_token($this);
        $id = $user['user']->id;
        $old_password = $user['password'];

        // login first
        $response = $this->json('POST', '/auth/login',
            ['id'=>$id, 'password'=>$old_password]);
        $response->assertResponseOk();
        $response->seeJsonStructure(['token']);

        $jwc_token = json_decode($response->response->getContent())->token;

        // change password
        $response = $this->json('POST', '/auth/change_password',
            ['password' => $new_password],
            ['Authorization'=>'bearer '.$jwc_token]);
        $response->assertResponseStatus(204);

        // old password is no longer valid
        $response = $this->json('POST', '/auth/login',
        ['id'=>$id, 'password'=>$old_password]);
        $response->assertResponseStatus(401);

        // use new password instead old one
        $response = $this->json('POST', '/auth/login',
        ['id'=>$id, 'password'=>$new_password]);
        $response->assertResponseOk();
    }
}
