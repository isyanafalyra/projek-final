<x-guest-layout>
    <h4 class="text-white mb-4 text-center fw-bold">Create Account</h4>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div class="mb-3">
            <label for="name" class="form-label form-label-custom">Full Name</label>
            <input id="name" class="form-control form-control-custom @error('name') is-invalid @enderror" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" />
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Email Address -->
        <div class="mb-3">
            <label for="email" class="form-label form-label-custom">Email Address</label>
            <input id="email" class="form-control form-control-custom @error('email') is-invalid @enderror" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" />
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-3">
            <label for="password" class="form-label form-label-custom">Password</label>
            <input id="password" class="form-control form-control-custom @error('password') is-invalid @enderror" type="password" name="password" required autocomplete="new-password" />
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div class="mb-4">
            <label for="password_confirmation" class="form-label form-label-custom">Confirm Password</label>
            <input id="password_confirmation" class="form-control form-control-custom" type="password" name="password_confirmation" required autocomplete="new-password" />
        </div>

        <div class="d-flex align-items-center justify-content-between mt-4">
            <a class="text-info text-decoration-none small" href="{{ route('login') }}">
                Already registered?
            </a>

            <button type="submit" class="btn btn-primary-custom">
                Register
            </button>
        </div>

        <div class="text-center mt-4 pt-2 border-top border-secondary">
            <span class="text-secondary small">Already have an account? </span>
            <a href="{{ route('login') }}" class="text-info text-decoration-none small fw-bold">Sign In</a>
        </div>
    </form>
</x-guest-layout>
