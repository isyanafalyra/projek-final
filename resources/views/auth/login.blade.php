<x-guest-layout>
    <!-- Session Status -->
    @if (session('status'))
        <div class="alert alert-success mb-4" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <h4 class="text-white mb-4 text-center fw-bold">Sign In</h4>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div class="mb-3">
            <label for="email" class="form-label form-label-custom">Email Address</label>
            <input id="email" class="form-control form-control-custom @error('email') is-invalid @enderror" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" />
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-3">
            <label for="password" class="form-label form-label-custom">Password</label>
            <input id="password" class="form-control form-control-custom @error('password') is-invalid @enderror" type="password" name="password" required autocomplete="current-password" />
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Remember Me -->
        <div class="form-check mb-4">
            <input id="remember_me" type="checkbox" class="form-check-input" style="background-color: var(--bg-primary); border-color: var(--card-border);" name="remember">
            <label for="remember_me" class="form-check-label text-secondary small">Remember me</label>
        </div>

        <div class="d-flex align-items-center justify-content-between mt-4">
            @if (Route::has('password.request'))
                <a class="text-info text-decoration-none small" href="{{ route('password.request') }}">
                    Forgot your password?
                </a>
            @endif

            <button type="submit" class="btn btn-primary-custom">
                Log in
            </button>
        </div>

        <div class="text-center mt-4 pt-2 border-top border-secondary">
            <span class="text-secondary small">Don't have an account? </span>
            <a href="{{ route('register') }}" class="text-info text-decoration-none small fw-bold">Register</a>
        </div>
    </form>
</x-guest-layout>
