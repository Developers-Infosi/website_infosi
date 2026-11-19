<?php

/**
 * Guardar como: app/Http/Middleware/Reviewer.php
 *
 * Acções de validação: mudar o estado de uma nota verbal e repor uma nota
 * removida. Só Administrador e Técnico. O Delegado nunca chega aqui.
 *
 * Existe porque o middleware Administrador é demasiado apertado (deixa de
 * fora o Técnico) e o Delegate é demasiado largo (deixa entrar o Delegado).
 */

namespace App\Http\Middleware;

use App\Classes\Logger;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Reviewer
{
    private $Logger;

    /** Níveis que validam notas verbais */
    private $allowed = ['Técnico', 'Administrador'];

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
