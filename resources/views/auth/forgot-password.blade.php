{{-- Guardar como: resources/views/auth/forgot-password.blade.php --}}
@extends('layouts.auth')

@section('page_title', 'Reset password')
@section('card_title', __('Reset your password'))
@section('card_intro', __('Enter the email address of your account. We will send you a link to choose a new password.'))

@section('content')

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="field">
            <label for="email">{{ __('Email') }}</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
        </div>

        <button type="submit" class="btn">{{ __('Send reset link') }}</button>
    </form>

@endsection

@section('card_footer')
    <a href="{{ route('login') }}">{{ __('Back to sign in') }}</a>
@endsection
