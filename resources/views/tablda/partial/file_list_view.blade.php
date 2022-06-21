<a target="_blank" href="/storage/{{ $file->filepath.$file->filename }}">
    @if($file->is_img)
        <img class="_img_preview" src="/storage/{{ $file->filepath.$file->filename }}" height="30">
    @else
        {{ $file->filename }}
    @endif
</a>