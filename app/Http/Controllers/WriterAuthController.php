<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\WriterUser;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Carbon\Carbon;

class WriterAuthController extends Controller {
    private function jwt(WriterUser $user) {
        $signer = new Sha256();
        $token = (new Builder())->setIssuer(env('APP_URL'))
            ->setAudience(env('APP_URL'))
            ->setId(uniqid(), true)
            ->setIssuedAt(Carbon::now()->getTimestamp())
            ->setNotBefore(Carbon::now()->getTimestamp())
            ->setExpiration(Carbon::now()->getTimestamp() + env('JWT_EXPIRE'))
            ->set('uid', $user->id)
            ->sign($signer, env('JWT_SECRET'))
            ->getToken();

        return $token;
    }

    public function authenticate(Request $request) {
        $this->validate($request, [
            'id'       => ['required', 'string'],
            'password' => ['required', 'string']
        ]);

        $user = WriterUser::find($request->input('id'));

        if(!$user)
            throw new HttpException(401);

        if (Hash::check($request->input('password'), $user->password))
            return ['token' => $this->jwt($user)->__toString()];
        else
            throw new HttpException(401);
    }

    public function user_info(Request $request) {
        $this->middleware('auth:writer');
        return response()->json($request->user('writer'), 200);
        //return response()->json(Auth::('writer')->user(), 200);
    }
}
