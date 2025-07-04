@extends('layouts.auth.default')

@section('content')
<style>
   
    
    .login-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
        padding: 40px;
        width: 100%;
        max-width: 600px;
        position: relative;
        overflow: hidden;
    }
    
    .login-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
    }
    
    .welcome-title {
        font-size: 28px;
        font-weight: 700;
        color: #053178;
        text-align: center;
        margin-bottom: 8px;
    }
    
    .welcome-subtitle {
        font-size: 14px;
        color: #718096;
        text-align: center;
        margin-bottom: 30px;
    }
    
    .modern-input-group {
        position: relative;
        margin-bottom: 20px;
    }
    
    .modern-input {
        width: 100%;
        padding: 16px 50px 16px 16px;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        font-size: 14px;
        background: #f8fafc;
        transition: all 0.3s ease;
        outline: none;
    }
    
    .modern-input:focus {
        border-color: #11b8aa;
        background: white;
        box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
    }
    
    .modern-input.is-invalid {
        border-color: #e53e3e;
        background: #fed7d7;
    }
    
    .input-icon {
        position: absolute;
        right: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #a0aec0;
        font-size: 16px;
    }
    
    .modern-input:focus + .input-icon {
        color: #11b8aa;
    }
    
    .invalid-feedback {
        color: #e53e3e;
        font-size: 12px;
        margin-top: 4px;
        margin-left: 4px;
    }
    
    .recaptcha-container {
        margin: 20px 0;
        display: flex;
        justify-content: center;
    }
    
    .recaptcha-error {
        background: #fed7d7;
        color: #c53030;
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 12px;
        margin-top: 10px;
        margin-bottom: 5px;
        text-align: center;
    }
    
    .remember-container {
        display: flex;
        align-items: center;
        margin: 20px 0;
        font-size: 14px;
        color: #4a5568;
    }
    
    .remember-checkbox {
        margin-right: 8px;
        transform: scale(1.1);
    }
    
    .login-button {
        width: 100%;
        padding: 16px;
        background: linear-gradient(135deg, #053178 0%, #053178);
        border: none;
        border-radius: 12px;
        color: white;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-bottom: 20px;
    }
    
    .login-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
    }
    
    .login-button:active {
        transform: translateY(0);
    }
    
    .forgot-password {
        text-align: center;
        margin: 15px 0;
    }
    
    .forgot-password a {
        color: #11b8aa;
        text-decoration: none;
        font-size: 14px;
        transition: color 0.3s ease;
    }
    
    .forgot-password a:hover {
        color: #2b6cb0;
    }
    
    .signup-link {
        text-align: center;
        margin-top: 5px;
        padding-top: 20px;
        border-top: 1px solid #e2e8f0;
    }
    
    .signup-link a {
        background: linear-gradient(135deg, #11b8aa 0%, #11b8aa 100%);
        color: white;
        padding: 12px 30px;
        border-radius: 25px;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        display: inline-block;
        transition: all 0.3s ease;
    }
    
    .signup-link a:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(72, 187, 120, 0.4);
        color: white;
        text-decoration: none;
    }
    
    .demo-credentials {
        background: linear-gradient(135deg, #ebf8ff 0%, #bee3f8 100%);
        border: 1px solid #90cdf4;
        border-radius: 12px;
        padding: 20px;
        margin-top: 20px;
    }
    
    .demo-credentials .demo-title {
        font-weight: 600;
        color: #2b6cb0;
        margin-bottom: 5px;
    }
    
    .demo-credentials .demo-info {
        font-size: 12px;
        color: #2c5282;
        margin-bottom: 15px;
    }
    
    .demo-credentials .demo-info:last-child {
        margin-bottom: 0;
    }
    .shadow-sm{
        box-shadow: 0 0 0px !important;
    }
    .card {
  box-shadow: 0 0 0px ;
}

.card {
  position: relative;
  display: flex;
  flex-direction: column;
  min-width: 0;
  word-wrap: break-word;
  background-color: transparent !important;
  background-clip: border-box;
  border: 0 solid rgba(0,0,0,.125);
  border-radius: .25rem;
}
</style>



    <div class="login-card">
         <div class="login-logo">
        <a href="{{ url('/') }}"><img src="/images/logo.png" alt="{{setting('app_name')}}"></a>
    </div>
        <p class="welcome-subtitle">Connectez-vous à votre compte</p>

        <form id="login-form" action="{{ url('/login') }}" method="post" onsubmit="return validateRecaptcha()">
            {!! csrf_field() !!}

            <div class="modern-input-group">
                <input 
                    value="{{ old('email') }}" 
                    type="email" 
                    class="modern-input {{ $errors->has('email') ? 'is-invalid' : '' }}" 
                    name="email" 
                    placeholder="Adresse e-mail" 
                    aria-label="{{ __('auth.email') }}"
                >
                <i class="fas fa-envelope input-icon"></i>
                @if ($errors->has('email'))
                    <div class="invalid-feedback">
                        {{ $errors->first('email') }}
                    </div>
                @endif
            </div>

           <div class="modern-input-group position-relative">
    <input 
        value="{{ old('password') }}" 
        type="password" 
        class="modern-input {{ $errors->has('password') ? 'is-invalid' : '' }}" 
        name="password" 
        id="password-field"
        placeholder="Mot de passe" 
        aria-label="{{ __('auth.password') }}"
    >
    <i class="fas fa-lock input-icon"></i>
    
    <!-- Icône œil -->
    <i class="fas fa-eye toggle-password input-icon" style="margin-right: 25px" id="togglePassword"></i>

    @if ($errors->has('password'))
        <div class="invalid-feedback">
            {{ $errors->first('password') }}
        </div>
    @endif
</div>
<script>
    const togglePassword = document.getElementById('togglePassword');
    const passwordField = document.getElementById('password-field');

    togglePassword.addEventListener('click', function () {
        const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordField.setAttribute('type', type);

        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });
</script>


  
            {{-- Ajouter le reCAPTCHA --}}
            <div class="recaptcha-container">
                {!! NoCaptcha::display() !!}
                @if ($errors->has('g-recaptcha-response'))
                    <div class="invalid-feedback d-block">
                        {{ $errors->first('g-recaptcha-response') }}
                    </div>
                @endif
            </div>

            {{-- Div pour afficher le message d'erreur si le reCAPTCHA n'est pas validé --}}
            <div id="recaptcha-error" class="recaptcha-error" style="display:none;">
                Veuillez valider le reCAPTCHA avant de soumettre le formulaire.
            </div>

            <button type="submit" class="login-button">
                Se connecter
            </button>

          <div class="signup-link">
    <span style="color: #718096; font-size: 14px;">Pas encore de compte ?</span><br><br>
    <a href="https://wic-doctor.com/inscription-professionnel/inscription.html">S'inscrire</a>
</div>


            @if (config('installer.demo_app'))
                <div class="demo-credentials">
                    <div class="demo-title">Admin</div>
                    <div class="demo-info">User: admin@demo.com | Password: 123456</div>
                    <div class="demo-title">Clinic Owner</div>
                    <div class="demo-info">User: clinic@demo.com | Password: 123456</div>
                    <div class="demo-title">Customer</div>
                    <div class="demo-info">User: customer@demo.com | Password: 123456</div>
                </div>
            @endif
        </form>
    </div>

<script src="https://www.google.com/recaptcha/api.js?ver=3.0"></script>

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