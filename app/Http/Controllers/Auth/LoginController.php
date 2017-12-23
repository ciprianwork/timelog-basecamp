<?php

namespace App\Http\Controllers\Auth;

use App\Facades\Data;
use App\Http\Controllers\Controller;
use App\Models\Project;
use function flash;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use function title;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm()
    {
        title('Login');

        return view('auth.login');
    }

    /**
     * Handle a login request to the application.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {

            $this->setup();

            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    protected function setup()
    {
        // show welcome message
        flash('Welcome ' . user()->name . '!', 'success');

        $this->addUserProjects();

        // refresh data on login
        Data::getUserMonthlyHours(true);
        Data::getUserProjectlyHours(true);
    }

    protected function addUserProjects()
    {
        $projects = getAllProjects();

        foreach ($projects as $projectId => $name) {

            $projectInstance = Project::firstOrNew([
                'user_id' => user()->id,
                'project_id' => $projectId,
            ]);

            $projectInstance->user_id = user()->id;
            $projectInstance->project_id = $projectId;
            $projectInstance->project_name = $name;

            $projectInstance->save();
        }
    }
}
