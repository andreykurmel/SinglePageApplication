@if ($socialProviders)
    <div class="flex flex--space flex--center-v">
        <a class="btn btn-block btn-social btn-facebook autn-btn" href="{{ url('auth/facebook/login') }}">
            <i class="fab fa-facebook fa-2x"></i>
            Facebook
        </a>
        <a class="btn btn-block btn-social btn-linkedin autn-btn" href="{{ url('auth/linkedin/login') }}">
            <i class="fab fa-linkedin fa-2x"></i>
            LinkedIn
        </a>
        <a class="autn-btn auth-google" href="{{ url('auth/google/login') }}"></a>
    </div>
@endif