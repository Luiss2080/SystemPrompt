@csrf
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<p>
    <label>Titulo</label><br>
    <input type="text" name="titulo" maxlength="100" required value="{{ old('titulo', $prompt->titulo ?? '') }}">
</p>
<p>
    <label>Contenido</label><br>
    <textarea name="contenido" rows="8" required>{{ old('contenido', $prompt->contenido ?? '') }}</textarea>
</p>
<p>
    <label>Descripcion</label><br>
    <textarea name="descripcion" rows="2">{{ old('descripcion', $prompt->descripcion ?? '') }}</textarea>
</p>
<p>
    <label>Categoria</label><br>
    <select name="categoria_id">
        <option value="">Sin categoria</option>
        @foreach($categorias as $categoria)
            <option value="{{ $categoria->id }}" @selected(old('categoria_id', $prompt->categoria_id ?? null) == $categoria->id)>{{ $categoria->nombre }}</option>
        @endforeach
    </select>
</p>
<p>
    <label>IA destino</label><br>
    <input type="text" name="ia_destino" maxlength="50" value="{{ old('ia_destino', $prompt->ia_destino ?? '') }}">
</p>
<p>
    <label>Etiquetas</label><br>
    @php $seleccion = old('etiquetas', isset($prompt) ? $prompt->etiquetas->pluck('id')->all() : []); @endphp
    @foreach($etiquetas as $etiqueta)
        <label><input type="checkbox" name="etiquetas[]" value="{{ $etiqueta->id }}" @checked(in_array($etiqueta->id, $seleccion))> {{ $etiqueta->nombre }}</label>
    @endforeach
</p>
<p>
    <label><input type="checkbox" name="es_publico" value="1" @checked(old('es_publico', $prompt->es_publico ?? false))> Publico</label>
</p>
