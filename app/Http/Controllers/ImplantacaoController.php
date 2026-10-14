<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Finishes an automatic deploy on hosting without SSH: the GitHub workflow
 * uploads the files by FTP and then calls this endpoint, which runs only a
 * fixed list of commands (migrations + cache refresh). It does not exist
 * unless DEPLOY_TOKEN (32+ characters) is set in .env, and answers only to
 * that exact token.
 */
class ImplantacaoController extends Controller
{
    public function concluir(Request $request): JsonResponse
    {
        $token = (string) config('app.deploy_token');

        if (strlen($token) < 32) {
            abort(404);
        }

        if (! hash_equals($token, (string) $request->header('X-Deploy-Token'))) {
            Log::warning('Tentativa de concluir implantação com token inválido, IP '.$request->ip());
            abort(403);
        }

        $resultado = [];

        try {
            foreach (['migrate' => ['--force' => true], 'optimize:clear' => [], 'config:cache' => []] as $comando => $argumentos) {
                $codigo = Artisan::call($comando, $argumentos);
                $resultado[$comando] = ['codigo' => $codigo, 'saida' => trim(Artisan::output())];

                if ($codigo !== 0) {
                    return response()->json(['ok' => false, 'passos' => $resultado], 500);
                }
            }
        } catch (Throwable $e) {
            Log::error('Falha a concluir a implantação: '.$e->getMessage());

            return response()->json(['ok' => false, 'erro' => $e->getMessage(), 'passos' => $resultado], 500);
        }

        return response()->json(['ok' => true, 'passos' => $resultado]);
    }
}
