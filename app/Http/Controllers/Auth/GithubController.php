<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GithubProvider;
use Throwable;

class GithubController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function redirect()
    {
        return $this->github()->redirect();
    }

    public function handle()
    {
        $githubUser = $this->github()->stateless()->user();

        ray($githubUser);
        $user = $this->findGithubUser($githubUser->getEmail());

        if ($user instanceof User) {
            ray('login user');
            return $this->loginUser($user);
        }

        $user = $this->findUser($githubUser->getEmail());

        if ($user instanceof User) {
            ray('already');
            $this->session()->flash('info', 'You already have an account.');

            return redirect()->route('sign-in')
                ->withInput(['email' => $githubUser->getEmail()]);
        }

        return $this->populateAndRedirectToSignUp($githubUser);
    }

    public function session()
    {
        return Session::getFacadeRoot();
    }

    protected function github(): GithubProvider
    {
        return Socialite::driver('github');
    }

    private function findGithubUser(string $email)
    {
        return User::where([
            ['email', '=', $email],
            ['github', '=', true]
        ])->first();
    }

    private function findUser(string $email)
    {
        return User::firstWhere([
            'email' => $email,
        ]);
    }


    private function loginUser(User $user)
    {
        Auth::login($user, true);

        return redirect()->intended('dashboard');
    }

    private function populateAndRedirectToSignUp($githubUser)
    {
        request()->session()->put('githubUser', [
            'name' => $githubUser->getName(),
            'email' => $githubUser->getEmail(),
            'avatar_url' => $githubUser->getAvatar(),
        ]);

        return redirect()->route('sign-up');
    }
}
