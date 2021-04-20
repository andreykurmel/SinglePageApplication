@extends('tablda.app')

@section('page-title', settings('app_name'))

@section('content')
    <div class="full-frame" style="margin: 30px;">
        <div id="retainable-rss-embed"
             data-rss="https://medium.com/feed/{{ '@tablda' }}"
             data-maxcols="3"
             data-layout="grid"
             data-poststyle="inline"
             data-readmore="Read the rest"
             data-buttonclass="btn btn-primary"
             data-offset="-100"></div>
    </div>
@endsection

@push('scripts')
    <script>
        var container = document.getElementById("retainable-rss-embed");
        if (container) {
            var css = document.createElement('link');
            css.href = "{{ $clear_url }}/assets/plugins/retainable.css";
            css.rel = "stylesheet";
            document.getElementsByTagName('head')[0].appendChild(css);
            var script = document.createElement('script');
            script.src = "{{ $clear_url }}/assets/plugins/retainable.js";
            document.getElementsByTagName('body')[0].appendChild(script);
        }
    </script>
@endpush