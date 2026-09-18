<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Etiqueta;
use App\Models\Prompt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BuscadorController extends Controller
{
    /**
     * Pagina del buscador global. Acepta ?query= (formulario del header) o ?q=.
     */
    public function index(Request $request)
    {
        $termino = trim((string) ($request->input('query', $request->input('q', ''))));
        $resultados = strlen($termino) >= 2 ? $this->buscar($termino, 10) : [];

        return view('buscador.index', compact('termino', 'resultados'));
    }

    /**
     * Busqueda en tiempo real (AJAX)
     */
    public function search(Request $request)
    {
        $termino = trim((string) $request->input('q', ''));

        if (strlen($termino) < 2) {
            return response()->json(['resultados' => []]);
        }

        return response()->json(['resultados' => $this->buscar($termino, 5)]);
    }

    /**
     * Prompts propios o publicos, categorias y etiquetas que coinciden con el termino.
     */
    private function buscar(string $termino, int $limite): array
    {
        $like = '%' . addcslashes($termino, '%_\\') . '%';

        $prompts = Prompt::where(function ($q) {
                $q->where('user_id', Auth::id())->orWhere('es_publico', true);
            })
            ->where(function ($q) use ($like) {
                $q->where('titulo', 'like', $like)->orWhere('contenido', 'like', $like);
            })
            ->limit($limite)
            ->get()
            ->map(fn ($prompt) => [
                'titulo' => $prompt->titulo,
                'descripcion' => mb_substr($prompt->descripcion ?? $prompt->contenido, 0, 80),
                'tipo' => 'Prompt',
                'icono' => 'file-alt',
                'url' => route('prompts.show', $prompt->id),
            ]);

        $categorias = Categoria::where('nombre', 'like', $like)
            ->limit($limite)
            ->get()
            ->map(fn ($categoria) => [
                'titulo' => $categoria->nombre,
                'descripcion' => $categoria->descripcion ?? 'Categoria',
                'tipo' => 'Categoria',
                'icono' => 'folder',
                'url' => route('prompts.index', ['categoria_id' => $categoria->id]),
            ]);

        $etiquetas = Etiqueta::where('nombre', 'like', $like)
            ->limit($limite)
            ->get()
            ->map(fn ($etiqueta) => [
                'titulo' => $etiqueta->nombre,
                'descripcion' => 'Etiqueta',
                'tipo' => 'Etiqueta',
                'icono' => 'tag',
                'url' => route('prompts.index', ['etiqueta' => $etiqueta->nombre]),
            ]);

        return $prompts->concat($categorias)->concat($etiquetas)->values()->all();
    }
}
