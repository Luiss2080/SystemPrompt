<?php

namespace App\Http\Controllers;

use App\Models\Prompt;
use App\Models\Categoria;
use App\Models\Etiqueta;
use App\Models\Version;
use App\Models\Actividad;
use App\Models\Compartido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PromptController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Prompt::with(['categoria', 'etiquetas', 'user'])
            ->where('user_id', Auth::id());

        // Búsqueda por palabra clave
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('titulo', 'like', "%{$buscar}%")
                  ->orWhere('contenido', 'like', "%{$buscar}%")
                  ->orWhere('descripcion', 'like', "%{$buscar}%");
            });
        }

        // Filtro por categoría
        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        // Filtro por IA destino
        if ($request->filled('ia_destino')) {
            $query->where('ia_destino', $request->ia_destino);
        }

        // Filtro por favoritos
        if ($request->filled('favoritos')) {
            $query->where('es_favorito', true);
        }

        // Filtro por etiqueta
        if ($request->filled('etiqueta')) {
            $query->whereHas('etiquetas', function($q) use ($request) {
                $q->where('nombre', $request->etiqueta);
            });
        }

        // Ordenamiento
        $orden = $request->get('orden', 'reciente');
        switch ($orden) {
            case 'titulo':
                $query->orderBy('titulo');
                break;
            case 'uso':
                $query->orderBy('veces_usado', 'desc');
                break;
            case 'modificacion':
                $query->orderBy('fecha_modificacion', 'desc');
                break;
            default:
                $query->latest();
        }

        $prompts = $query->paginate(15);
        $categorias = Categoria::all();
        $etiquetas = Etiqueta::all();

        return view('prompts.index', compact('prompts', 'categorias', 'etiquetas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categorias = Categoria::all();
        $etiquetas = Etiqueta::all();
        return view('prompts.create', compact('categorias', 'etiquetas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:100',
            'contenido' => 'required|string',
            'descripcion' => 'nullable|string',
            'categoria_id' => 'nullable|exists:categorias,id',
            'ia_destino' => 'nullable|string|max:50',
            'es_publico' => 'boolean',
            'etiquetas' => 'array',
            'etiquetas.*' => 'exists:etiquetas,id'
        ]);

        $prompt = Prompt::create([
            'user_id' => Auth::id(),
            'titulo' => $validated['titulo'],
            'contenido' => $validated['contenido'],
            'descripcion' => $validated['descripcion'] ?? null,
            'categoria_id' => $validated['categoria_id'] ?? null,
            'ia_destino' => $validated['ia_destino'] ?? null,
            'es_publico' => $request->boolean('es_publico'),
            'fecha_creacion' => now(),
            'version_actual' => 1
        ]);

        // Asignar etiquetas
        if ($request->filled('etiquetas')) {
            $prompt->etiquetas()->attach($request->etiquetas);
        }

        // Crear primera versión
        Version::create([
            'prompt_id' => $prompt->id,
            'numero' => 1,
            'contenido' => $prompt->contenido,
            'fecha_version' => now()
        ]);

        // Registrar actividad
        Actividad::create([
            'prompt_id' => $prompt->id,
            'user_id' => Auth::id(),
            'accion' => 'creado',
            'descripcion' => 'Prompt creado',
            'fecha' => now()
        ]);

        return redirect()->route('prompts.show', $prompt)
            ->with('success', 'Prompt creado exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(Prompt $prompt)
    {
        $this->authorize('view', $prompt);
        
        $prompt->load(['categoria', 'etiquetas', 'versiones', 'actividades.user', 'compartidos']);
        
        return view('prompts.show', compact('prompt'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Prompt $prompt)
    {
        $this->authorize('update', $prompt);
        
        $categorias = Categoria::all();
        $etiquetas = Etiqueta::all();
        
        return view('prompts.edit', compact('prompt', 'categorias', 'etiquetas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Prompt $prompt)
    {
        $this->authorize('update', $prompt);

        $validated = $request->validate([
            'titulo' => 'required|string|max:100',
            'contenido' => 'required|string',
            'descripcion' => 'nullable|string',
            'categoria_id' => 'nullable|exists:categorias,id',
            'ia_destino' => 'nullable|string|max:50',
            'es_publico' => 'boolean',
            'etiquetas' => 'array',
            'etiquetas.*' => 'exists:etiquetas,id',
            'motivo_cambio' => 'nullable|string'
        ]);

        // Verificar si el contenido cambió para crear nueva versión
        $contenidoCambio = $prompt->contenido !== $validated['contenido'];
        
        if ($contenidoCambio) {
            // Crear nueva versión
            $prompt->version_actual++;
            Version::create([
                'prompt_id' => $prompt->id,
                'numero' => $prompt->version_actual,
                'contenido' => $validated['contenido'],
                'contenido_anterior' => $prompt->contenido,
                'motivo_cambio' => $validated['motivo_cambio'] ?? null,
                'fecha_version' => now()
            ]);
        }

        $prompt->update([
            'titulo' => $validated['titulo'],
            'contenido' => $validated['contenido'],
            'descripcion' => $validated['descripcion'] ?? null,
            'categoria_id' => $validated['categoria_id'] ?? null,
            'ia_destino' => $validated['ia_destino'] ?? null,
            'es_publico' => $request->boolean('es_publico'),
            'fecha_modificacion' => now()
        ]);

        // Actualizar etiquetas
        if ($request->has('etiquetas')) {
            $prompt->etiquetas()->sync($request->etiquetas);
        }

        // Registrar actividad
        Actividad::create([
            'prompt_id' => $prompt->id,
            'user_id' => Auth::id(),
            'accion' => 'editado',
            'descripcion' => $contenidoCambio ? 'Contenido modificado - versión ' . $prompt->version_actual : 'Metadatos actualizados',
            'fecha' => now()
        ]);

        return redirect()->route('prompts.show', $prompt)
            ->with('success', 'Prompt actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Prompt $prompt)
    {
        $this->authorize('delete', $prompt);
        
        $prompt->delete();
        
        return redirect()->route('prompts.index')
            ->with('success', 'Prompt eliminado exitosamente');
    }

    /**
     * Toggle favorite status
     */
    public function toggleFavorito(Prompt $prompt)
    {
        $this->authorize('update', $prompt);
        
        $prompt->es_favorito = !$prompt->es_favorito;
        $prompt->save();

        Actividad::create([
            'prompt_id' => $prompt->id,
            'user_id' => Auth::id(),
            'accion' => $prompt->es_favorito ? 'marcado_favorito' : 'desmarcado_favorito',
            'descripcion' => $prompt->es_favorito ? 'Marcado como favorito' : 'Desmarcado como favorito',
            'fecha' => now()
        ]);

        return back()->with('success', $prompt->es_favorito ? 'Agregado a favoritos' : 'Removido de favoritos');
    }

    /**
     * Increment usage counter
     */
    public function incrementarUso(Prompt $prompt)
    {
        $this->authorize('view', $prompt);
        
        $prompt->increment('veces_usado');

        Actividad::create([
            'prompt_id' => $prompt->id,
            'user_id' => Auth::id(),
            'accion' => 'usado',
            'descripcion' => 'Prompt copiado/usado',
            'fecha' => now()
        ]);

        return response()->json(['success' => true, 'veces_usado' => $prompt->veces_usado]);
    }

    /**
     * Share prompt with someone
     */
    public function compartir(Request $request, Prompt $prompt)
    {
        $this->authorize('view', $prompt);

        $validated = $request->validate([
            'nombre_destinatario' => 'required|string|max:100',
            'email_destinatario' => 'required|email|max:100',
            'notas' => 'nullable|string'
        ]);

        Compartido::create([
            'prompt_id' => $prompt->id,
            'nombre_destinatario' => $validated['nombre_destinatario'],
            'email_destinatario' => $validated['email_destinatario'],
            'notas' => $validated['notas'] ?? null,
            'fecha_compartido' => now()
        ]);

        Actividad::create([
            'prompt_id' => $prompt->id,
            'user_id' => Auth::id(),
            'accion' => 'compartido',
            'descripcion' => "Compartido con {$validated['email_destinatario']}",
            'fecha' => now()
        ]);

        return back()->with('success', 'Prompt compartido exitosamente');
    }

    /**
     * Get version history
     */
    public function historial(Prompt $prompt)
    {
        $this->authorize('view', $prompt);
        
        $actividades = $prompt->actividades()
            ->with('user')
            ->latest('fecha')
            ->paginate(20);

        return view('prompts.historial', compact('prompt', 'actividades'));
    }

    /**
     * Restore a specific version
     */
    public function restaurarVersion(Prompt $prompt, Version $version)
    {
        $this->authorize('update', $prompt);

        if ($version->prompt_id !== $prompt->id) {
            abort(403);
        }

        // Crear nueva versión con el contenido restaurado
        $prompt->version_actual++;
        Version::create([
            'prompt_id' => $prompt->id,
            'numero' => $prompt->version_actual,
            'contenido' => $version->contenido,
            'contenido_anterior' => $prompt->contenido,
            'motivo_cambio' => "Restaurado desde versión {$version->numero}",
            'fecha_version' => now()
        ]);

        $prompt->update([
            'contenido' => $version->contenido,
            'fecha_modificacion' => now()
        ]);

        Actividad::create([
            'prompt_id' => $prompt->id,
            'user_id' => Auth::id(),
            'accion' => 'restaurado',
            'descripcion' => "Versión {$version->numero} restaurada",
            'fecha' => now()
        ]);

        return redirect()->route('prompts.show', $prompt)
            ->with('success', 'Versión restaurada exitosamente');
    }
}
