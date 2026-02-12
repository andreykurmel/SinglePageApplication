<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Select File:</title>
    <link media="all" type="text/css" rel="stylesheet" href="{{ mix('assets/css/tablda-app.css') }}">
    <style>
        .img_wrapper {
            width: 300px;
            height: 220px;
            display: flex;
            align-items: center;
            float: left;
            margin: 5px;
            justify-content: center;
            border: 1px solid #CCC;
            border-radius: 5px;
            position: relative;
        }
        .img_wrapper img {
            max-width: 100%;
            max-height: 100%;
            cursor: pointer;
            opacity: 0.8;
        }
        .img_wrapper img:hover {
            opacity: 1;
        }
        .img_wrapper .btn {
            position: absolute;
            right: 0;
            top: 0;
            z-index: 100;
        }
    </style>
</head>

<body>
    @foreach($files as $fl)
        <div class="img_wrapper" id="{{ str_replace('.','',$fl['name']) }}">
            {{--<button class="btn btn-sm btn-danger" onclick="deleteFile('{{ $fl['name'] }}')">&times;</button>--}}
            <img src="{{ $fl['path'] }}" onclick="returnFileUrl('{{ $fl['path'] }}')"/>
        </div>
    @endforeach

    <script src="{{ mix('assets/js/tablda/vendor.js') }}"></script>
    <script>
        // Helper function to get parameters from the query string.
        function getUrlParam( paramName ) {
            var reParam = new RegExp( '(?:[\?&]|&)' + paramName + '=([^&]+)', 'i' );
            var match = window.location.search.match( reParam );

            return ( match && match.length > 1 ) ? match[1] : null;
        }
        // Simulate user action of selecting a file to be returned to CKEditor.
        function returnFileUrl(fileUrl) {

            var funcNum = getUrlParam( 'CKEditorFuncNum' );
            window.opener.CKEDITOR.tools.callFunction( funcNum, fileUrl );
            window.close();
        }
        // Delete File
        function deleteFile(fileUrl) {
            axios.delete('/ckeditor/file-delete', {
                params: {
                    fname: fileUrl,
                }
            });
            $('#'+fileUrl).hide();
        }
    </script>
</body>


</html>