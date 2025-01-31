@extends('layouts.auth.default')

@section('content')
    <div class="card-body login-card-body">
        <p class="login-box-msg">{{ __('auth.login_title') }}</p>

        <form id="login-form" action="{{ url('/login') }}" method="post" onsubmit="return validateRecaptcha()">
            {!! csrf_field() !!}

            <div class="input-group mb-3">
                <input value="{{ old('email') }}" type="email" class="form-control {{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" placeholder="{{ __('auth.email') }}" aria-label="{{ __('auth.email') }}">
                <div class="input-group-append">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                </div>
                @if ($errors->has('email'))
                    <div class="invalid-feedback">
                        {{ $errors->first('email') }}
                    </div>
                @endif
            </div>

            <div class="input-group mb-3">
                <input value="{{ old('password') }}" type="password" class="form-control  {{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" placeholder="{{ __('auth.password') }}" aria-label="{{ __('auth.password') }}">
                <div class="input-group-append">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                </div>
                @if ($errors->has('password'))
                    <div class="invalid-feedback">
                        {{ $errors->first('password') }}
                    </div>
                @endif
            </div>

            {{-- Ajouter le reCAPTCHA --}}
            <div class="form-group">
                {!! NoCaptcha::display() !!}
                @if ($errors->has('g-recaptcha-response'))
                    <div class="invalid-feedback d-block">
                        {{ $errors->first('g-recaptcha-response') }}
                    </div>
                @endif
            </div>

            {{-- Div pour afficher le message d'erreur si le reCAPTCHA n'est pas validé --}}
            <div id="recaptcha-error" class="text-danger" style="display:none;">
                Veuillez valider le reCAPTCHA avant de soumettre le formulaire.
            </div>

            <div class="row mb-2">
                <div class="col-8"></div>
                <div class="col-4">
                    <button type="submit" class="btn btn-{{ setting("theme_color") }} btn-block">{{ __('auth.login') }}</button>
                </div>
            </div>

            @if (config('installer.demo_app'))
                <div class="my-4">
                    <div class="col-12 card card-outline card-primary">
                        <div class="card-body">
                            <div class="text-bold">Admin</div>
                            <small>User: admin@demo.com | Password: 123456</small>
                            <div class="text-bold mt-3">Clinic Owner</div>
                            <small>User: clinic@demo.com | Password: 123456</small>
                            <div class="text-bold mt-3">Customer</div>
                            <small>User: customer@demo.com | Password: 123456</small>
                        </div>
                    </div>
                </div>
            @endif
        </form>

<script src="https://www.google.com/recaptcha/api.js?ver=3.0"></script>

    </div>

    <script>
        function validateRecaptcha() {
            var recaptchaResponse = grecaptcha.getResponse();
            var errorDiv = document.getElementById("recaptcha-error");
            
            // Si le reCAPTCHA n'est pas validé
            if (recaptchaResponse.length === 0) {
                errorDiv.style.display = "block";  // Afficher le message d'erreur
                return false; // Ne pas soumettre le formulaire
            }
            
            // Si le reCAPTCHA est validé, cacher le message d'erreur
            errorDiv.style.display = "none";
            return true; // Soumettre le formulaire
        }
    </script>
@endsection
