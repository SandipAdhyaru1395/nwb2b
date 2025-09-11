@if ($category->children && $category->children->count())
    <optgroup label="{{ $category->name }}">
        @foreach ($category->children as $child)
            @include('_partials.edit_category_option', ['category' => $child])
        @endforeach
    </optgroup>
@else
    <option value="{{ $category->id }}"
        @selected(in_array($category->id, $brandCategories))>
        {{ $category->name }}
    </option>
@endif
