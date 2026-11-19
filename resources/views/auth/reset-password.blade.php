{{-- Guardar como: resources/views/auth/reset-password.blade.php --}}
@extends('layouts.auth')

@section('page_title', 'New password')
@section('card_title', __('Choose a new password'))
@section('card_intro', __('The link is valid for a limited time. Set a password you do not use anywhere else.'))

@section('content')

    <form method="POST" action="{{ route('password.update') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="field">
            <label for="email">{{ __('Email') }}</label>
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username">
        </div>

        <div class="field">
            <label for="password">{{ __('New password') }}</label>
            <input id="password" type="password" name="password" required autocomplete="new-password">
        </div>

        <div class="field">
            <label for="password_confirmation">{{ __('Confirm new password') }}</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
        </div>

        <button type="submit" class="btn">{{ __('Save new password') }}</button>
    </form>

@endsection

@section('card_footer')
    <a href="{{ route('login') }}">{{ __('Back to sign in') }}</a>
@endsection
