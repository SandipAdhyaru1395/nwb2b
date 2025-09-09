<li class="p-2" style="list-style-type: none">
    <label class="d-flex gap-2">
        <input type="checkbox" class="form-check-input" name="categories[]" value="{{ $category->id }}" @checked(in_array($category->id, $collectionCategories))>
        {{ $category->name }}
    </label>

    @if ($category->children && $category->children->count())
        <ul>
            @foreach ($category->children as $child)
                @include('_partials.category_checkbox', ['category' => $child])
            @endforeach
        </ul>
    @endif
</li>
