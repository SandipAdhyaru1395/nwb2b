<option value="{{ $category->id }}">
    {{ $prefix . $category->name }}
</option>

@if ($category->children && $category->children->count())
    @foreach ($category->children as $child)
        @include('_partials.parent_category_option', [
            'category' => $child,
            'prefix' => $prefix . '---------- '
        ])
    @endforeach
@endif
