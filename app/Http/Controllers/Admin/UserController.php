<?php



namespace App\Http\Controllers\Admin;

use App\Classes\Logger;
use App\Http\Controllers\Controller;
use App\Models\Log;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    private $Logger;

    /**
     * Lista branca de níveis atribuíveis. Nada fora daqui entra na coluna
     * level. Se existirem contas antigas com outro valor (COPS, por
     * exemplo), esse valor é acrescentado às opções só para aquela conta,
     * em levelOptions(), para que editar o nome não force uma mudança de
     * nível sem querer.
     */
    private $levels = ['Administrador', 'Técnico', 'Delegado', 'Convidado'];

    public function __construct()
    {
        $this->Logger = new Logger;
    }

    public function index()
    {
        $response['users'] = User::orderBy('name')->get();

        //Logger
        $this->Logger->log('info', 'Listou os Utilizadores');

        return view('admin.user.list.index', $response);
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function show($uuid)
    {
        $user = $this->findUser($uuid);

        if (! $this->canManage($user)) {
            return $this->denied();
        }

        $response['logs'] = Log::where('USER_ID', $user->id)->orderBy('id', 'desc')->get();
        $response['user'] = $user;

        //Logger
        $this->Logger->log('info', 'Visualizou o Utilizador ' . $user->name);

        return view('admin.user.details.index', $response);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function edit($uuid)
    {
        $user = $this->findUser($uuid);

        if (! $this->canManage($user)) {
            return $this->denied();
        }

        $response = [
            'user'        => $user,
            'levels'      => $this->levelOptions($user),
            // O select do nível só aparece quando isto for verdadeiro
            'canSetLevel' => $this->canSetLevel($user),
        ];

        //Logger
        $this->Logger->log('info', 'Entrou em editar o Utilizador ' . $user->name);

        return view('admin.user.edit.index', $response);
    }

    /**
     * Dados pessoais. A senha não passa por aqui.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $uuid)
    {
        $user = $this->findUser($uuid);

        if (! $this->canManage($user)) {
            return $this->denied();
        }

        $request->validateWithBag('updateData', [
            'name'  => ['required', 'string', 'max:255'],
            /*
             * A tabela users usa SoftDeletes e o email tem índice único.
             * Sem o whereNull, uma conta eliminada continua a bloquear o
             * seu email para sempre.
             */
            'email' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($user->id)->whereNull('deleted_at'),
            ],
        ]);

        $data = [
            'name'  => $request->input('name'),
            'email' => $request->input('email'),
        ];

        /*
         * O nível só é tocado por um Administrador, e nunca no próprio
         * registo. Se vier no pedido sem autorização, é ignorado e fica log.
         */
        if ($this->canSetLevel($user)) {
            $request->validateWithBag('updateData', [
                'level' => ['required', Rule::in($this->levelOptions($user))],
            ]);

            if ($request->input('level') !== $user->level) {
                $this->Logger->log(
                    'info',
                    'Alterou o nível do utilizador ' . $user->name . ' de ' . $user->level . ' para ' . $request->input('level')
                );
            }

            $data['level'] = $request->input('level');
        } elseif ($request->filled('level') && $request->input('level') !== $user->level) {
            $this->Logger->log(
                'info',
                '<b class="text-danger">Tentou alterar o nível de acesso sem permissão!</b> Utilizador ' . $user->name
            );

            return redirect()->back()->withInput()
                ->with('NoAuth', 'Não tem permissão para alterar o nível de acesso.');
        }

        $user->update($data);

        //Logger
        $this->Logger->log('info', 'Editou os dados do Utilizador ' . $user->name);

        return redirect()->route('admin.user.edit', $user)->with('edit', '1');
    }

    /**
     * Alteração de senha. Exige sempre a senha actual de quem está a
     * submeter: a própria, quando é o dono da conta; a do Administrador,
     * quando está a repor a senha de outra pessoa.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function updatePassword(Request $request, $uuid)
    {
        $user = $this->findUser($uuid);

        if (! $this->canManage($user)) {
            return $this->denied();
        }

        $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'current_password.required' => 'Confirme a operação com a sua senha actual.',
        ]);

        if (! Hash::check($request->input('current_password'), Auth::user()->password)) {
            $this->Logger->log('info', '<b class="text-danger">Falhou a confirmação de senha ao alterar a senha do utilizador ' . $user->name . '</b>');

            return redirect()->back()->withErrors([
                'current_password' => 'A senha actual não confere.',
            ], 'updatePassword');
        }

        $user->update([
            'password' => Hash::make($request->input('password')),
        ]);

        //Logger
        $this->Logger->log('info', 'Alterou a senha do Utilizador ' . $user->name);

        return redirect()->route('admin.user.edit', $user)->with('password', '1');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy($uuid)
    {
        if (Auth::user()->level !== 'Administrador') {
            return $this->denied();
        }

        $user = $this->findUser($uuid);

        if ((int) $user->id === (int) Auth::id()) {
            return redirect()->back()->with('NoAuth', 'Não pode eliminar a sua própria conta.');
        }

        if ($user->level === 'Administrador' && User::where('level', 'Administrador')->count() <= 1) {
            return redirect()->back()->with('NoAuth', 'Tem de existir pelo menos um Administrador.');
        }

        if (User::count() <= 1) {
            return redirect()->back();
        }

        // SoftDeletes: a linha fica na tabela com deleted_at preenchido

        //Logger
        $this->Logger->log('info', 'Eliminou o Utilizador ' . $user->name);

        $user->delete();

        return redirect()->back()->with('destroy', '1');
    }

    /* ===================== Apoio ===================== */

    /**
     * Resolve a conta pelo uuid da URL. Sem uuid válido, 404.
     *
     * @param  string  $uuid
     * @return \App\Models\User
     */
    private function findUser($uuid)
    {
        return User::where('uuid', $uuid)->firstOrFail();
    }

    /**
     * O Administrador gere qualquer conta; os restantes só a sua.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    private function canManage(User $user)
    {
        return Auth::user()->level === 'Administrador' || (int) Auth::id() === (int) $user->id;
    }

    /**
     * Opções de nível para uma conta: a lista branca mais o valor actual,
     * quando este é anterior à lista.
     *
     * @param  \App\Models\User  $user
     * @return array
     */
    private function levelOptions(User $user)
    {
        $options = $this->levels;

        if (! empty($user->level) && ! in_array($user->level, $options, true)) {
            $options[] = $user->level;
        }

        return $options;
    }

    /**
     * Quem pode mexer no nível de acesso: só o Administrador, e nunca no
     * próprio registo. Assim, um Delegado ou um Técnico nunca se promove,
     * e um Administrador não se despromova sozinho por engano.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    private function canSetLevel(User $user)
    {
        return Auth::user()->level === 'Administrador' && (int) Auth::id() !== (int) $user->id;
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    private function denied()
    {
        //Logger
        $this->Logger->log('info', '<b class="text-danger">Tentou aceder uma página não autorizada!</b>');

        return redirect()->route('admin.home')->with('NoAuth', '1');
    }
}
