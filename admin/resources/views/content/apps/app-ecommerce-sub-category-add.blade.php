<option value="{{ $category->id }}" >
    {{ $prefix . $category->name }}
</option>

@if ($category->children && $category->children->count())
    @foreach ($category->children as $child)
        @include('content.apps.app-ecommerce-sub-category-add', [
            'category' => $child,
            'prefix' => $prefix . '--- '
        ])
    @endforeach
@endif
