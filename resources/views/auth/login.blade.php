@extends('layouts.guest', ['title' => 'Sign in'])

@section('content')
    <div class="auth-card">
        <p class="auth-brand">Santani</p>
        <h1 class="auth-title">{{ __('Sign in') }}</h1>
        <p class="auth-sub">{{ __('Sign in to continue to your point of sale.') }}</p>

        <form method="POST" action="{{ route('login') }}" novalidate>
            @csrf

            <div class="field">
                <label for="username">{{ __('Username') }}</label>
                <input id="username" type="text" name="username" value="{{ old('username') }}"
                       class="input @error('username') invalid @enderror"
                       autocomplete="username" autocapitalize="none" autocorrect="off" spellcheck="false" autofocus required>
                @error('username') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="password">{{ __('Password') }}</label>
                <input id="password" type="password" name="password"
                       class="input @error('password') invalid @enderror"
                       autocomplete="current-password" required>
                @error('password') <span class="error">{{ $message }}</span> @enderror
            </div>

            <label class="auth-remember">
                <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                <span>{{ __('Remember me') }}</span>
            </label>

            <button type="submit" class="btn btn-primary btn-block auth-submit">{{ __('Sign in') }}</button>
        </form>

        <p class="auth-lang">
            <a href="{{ route('locale.switch', 'id') }}">Bahasa Indonesia</a>
            <span>·</span>
            <a href="{{ route('locale.switch', 'en') }}">English</a>
        </p>
    </div>
@endsection
