{{-- Guardar como: resources/views/auth/site/index.blade.php --}}
@extends('layouts.auth')

@section('page_title', 'Sign in')
@section('card_title', __('Sign in'))
@section('card_intro', __('Use the credentials issued by the summit secretariat.'))

@section('content')

    <form method="POST" action="{{ route('site.login.store') }}">
        @csrf

        <div class="field">
            <label for="email">{{ __('Email') }}</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
        </div>

        <div class="field">
            <label for="password">{{ __('Password') }}</label>
            <input id="password" type="password" name="password" required autocomplete="current-password">
        </div>

        <label class="remember" for="remember_me">
            <input id="remember_me" type="checkbox" name="remember">
            {{ __('Keep me signed in') }}
        </label>

        <button type="submit" class="btn">{{ __('Sign in') }}</button>
    </form>

@endsection

@section('card_footer')
    @if (Route::has('password.request'))
        <a href="{{ route('password.request') }}">{{ __('Forgot your password?') }}</a>
    @endif
    <a href="{{ route('site.declaration.index') }}">{{ __('Traveller Health Declaration') }}</a>
@endsection
