<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Helpers\LoginFunctions;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // override
        Fortify::authenticateUsing(function (Request $request) {
            $connection = env('VITE_USER_CONNECTION', 'mariadb');

            // se login é o heimdall, buscar permissões baseado no system_key
            if ($connection == 'heimdall') {
                $system_key = env('VITE_SYSTEM_KEY', 'projeto_base_laravel');
                $dadosLogin = $request->merge(['system_key' => $system_key])->toArray();
                $response = Http::post(env("HEIMDALL_URL", "http://heimdalldev.semsa") . '/api/login', $dadosLogin)?->json();

                // se não retornar sucesso, retornar erro
                if (!$response || !$response['success']) {
                    $msg = $response['message'] ?? "Erro no servidor";
                    $bag = ['email' => ' ', 'cpf_cnpj' => ' ', 'username' => ' ', "password" => $msg];
                    return redirect()->back()->withInput($request->except(["password", "_token"]))->withErrors($bag);
                    // return $this->sendFailedLoginResponse($request);
                }
                $dados = $response['data'];

                // $user = (object)($dados['user'] ?? null);
                // "user" => $user,
                $menus = LoginFunctions::montarMenusPorPermissoes($dados['items'] ?? []);
                $permissoes = LoginFunctions::filterCollectRoutes($dados['items']);
                session([
                    "heimdallToken" => $dados['token'],
                    "perfis" => $dados['perfis'],
                    "menus" => $menus,
                    "permissoes" => $permissoes,
                ]);
            }

            // etapas padrões do Laravel Fortify
            if ($connection == 'heimdall') $user = User::where('cpf_cnpj', $request->cpf_cnpj)->first();
            else $user = User::where('email', $request->email)->first();

            if ($user && Hash::check($request->password, $user->password)) return $user;
        });

        // padrões do Laravel Fortify
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())) . '|' . $request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
