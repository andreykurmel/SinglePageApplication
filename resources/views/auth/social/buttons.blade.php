@if ($socialProviders)
    <?php $colSize = 12 / count($socialProviders); ?>

    <div class="row pb-3 pt-2">
        @if (in_array('facebook', $socialProviders))
            <div class="col-{{ $colSize }} d-flex align-items-center justify-content-center">
                <a href="{{ url('auth/facebook/login') }}" class="btn-facebook">
                    <i class="fab fa-facebook fa-2x"></i>
                </a>
            </div>
        @endif

        @if (in_array('linkedin', $socialProviders))
            <div class="col-{{ $colSize }} d-flex align-items-center justify-content-center">
                <a href="{{ url('auth/linkedin/login') }}" class="btn-linkedin">
                    <i class="fab fa-linkedin fa-2x"></i>
                </a>
            </div>
        @endif

        @if (in_array('google', $socialProviders))
            <div class="col-{{ $colSize }} d-flex align-items-center justify-content-center">
                <a href="{{ url('auth/google/login') }}" class="btn-google">
                    <i class="fab fa-google-plus-square fa-2x"></i>
                </a>
            </div>
        @endif
    </div>

@endif