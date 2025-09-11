@if ($category->children && $category->children->count())
    <optgroup label="{{ $category->name }}">
        @foreach ($category->children as $child)
            @include('_partials.category_option', ['category' => $child])
        @endforeach
    </optgroup>
@else
    <option value="{{ $category->id }}"
        @selected(in_array($category->id, old('categories', [])))>
        {{ $category->name }}
    </option>
@endif
