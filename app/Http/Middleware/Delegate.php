<?php

/**
 * Guardar como: app/Http/Middleware/Delegate.php
 *
 * Guarda a área partilhada das notas verbais. Deixa entrar os três níveis
 * que têm alguma coisa a fazer ali:
 *  - Delegado: submete e gere as notas da sua delegação.
 *  - Técnico e Administrador: veem e validam as notas de todas as delegações.
 *
 * A separação entre "as minhas" e "todas" é feita no controlador, não aqui.
 * Este middleware só decide quem entra na porta.
 */

namespace App\Http\Middleware;

use App\Classes\Logger;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Delegate
{
    private $Logger;

    /** Níveis com acesso à área das notas verbais */
    private $allowed = ['Delegado', 'Técnico', 'Administrador'];

    public function __construct()
    {
        $this->Logger = new Logger;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        if (! in_array(Auth::user()->level, $this->allowed, true)) {
            //Logger
            $this->Logger->log('info', '<b class="text-danger">Tentou aceder uma página não autorizada!</b>');

            return redirect()->route('site.painel')
                ->with('NoAuth', 'Acesso negado. Não possui permissões suficientes para realizar esta ação.');
        }

        return $next($request);
    }
}
