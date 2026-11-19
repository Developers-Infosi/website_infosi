{{-- Guardar como: resources/views/auth/confirm-password.blade.php --}}
@extends('layouts.auth')

@section('page_title', 'Confirm password')
@section('card_title', __('Confirm your password'))
@section('card_intro', __('This is a protected area. Type your password again to continue.'))

@section('content')

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div class="field">
            <label for="password">{{ __('Password') }}</label>
            <input id="password" type="password" name="password" required autofocus autocomplete="current-password">
        </div>

        <button type="submit" class="btn">{{ __('Confirm') }}</button>
    </form>

@endsection
