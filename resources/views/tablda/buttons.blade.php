@if ($socialProviders)
    <div class="row">
        <div class="col-md-4 col-spaced">
            <a class="btn btn-block btn-social btn-facebook" href="{{ url('auth/facebook/login') }}">
                <i class="fab fa-facebook fa-2x"></i>
                Facebook
            </a>
        </div>
        <div class="col-md-4 col-spaced">
            <a class="btn btn-block btn-social btn-linkedin" href="{{ url('auth/linkedin/login') }}">
                <i class="fab fa-linkedin fa-2x"></i>
                LinkedIn
            </a>
        </div>
        <div class="col-md-4 col-spaced">
            <a class="btn btn-block btn-social btn-google" href="{{ url('auth/google/login') }}">
                <i class="fab fa-google-plus-square fa-2x"></i>
                Google+
            </a>
        </div>
    </div>
@endif