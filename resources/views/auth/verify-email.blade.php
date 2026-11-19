{{-- Guardar como: resources/views/auth/verify-email.blade.php --}}
@extends('layouts.auth')

@section('page_title', 'Verify email')
@section('card_title', __('Verify your email address'))
@section('card_intro', __('We sent a verification link to your inbox. Open it to activate the account. If it did not arrive, request another one.'))

@section('content')

    @if (session('status') == 'verification-link-sent')
        <div class="notice notice-info">
            {{ __('A new verification link has been sent to your email address.') }}
        </div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="btn">{{ __('Resend verification email') }}</button>
    </form>

@endsection

@section('card_footer')
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" style="background:none;border:0;padding:0;font:inherit;color:var(--green-deep);border-bottom:1px solid var(--gold);cursor:pointer;">
            {{ __('Sign out') }}
        </button>
    </form>
@endsection
