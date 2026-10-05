{{-- Field tersembunyi untuk meneruskan $orderQuery lewat form GET (tanpa data pribadi) --}}
@foreach ($orderQuery as $key => $value)
    @if (is_array($value))
        @foreach ($value as $item)
            <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
        @endforeach
    @else
        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
    @endif
@endforeach
