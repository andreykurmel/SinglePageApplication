@extends('tablda.app')

@section('page-title',  settings('app_name'))

@section('content')
    <input type="hidden" id="msg_val" value="{{ $msg ?? 'Incorrect Request!' }}">
@endsection

@push('scripts')
    <script>
        $(document).ready(() => {
            Swal({
                title: $("#msg_val").val(),
                confirmButtonText: 'OK',
                showCancelButton: false,
            }).then(() => {
                var url = (Cookies.get('last-url-cloud') || '/data');
                url += (url.indexOf('?') > -1 ? '&' : '?') + 'opn=ucloud';
                location.href = url;
            });
        });
    </script>
@endpush
