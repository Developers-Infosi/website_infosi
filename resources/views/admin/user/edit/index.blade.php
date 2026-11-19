{{-- Guardar como: resources/views/admin/user/edit/index.blade.php --}}
@extends('layouts.merge.dashboard')
@section('titulo', 'Editar Conta')
@section('content')

    @if (session('edit'))
        <div class="alert alert-success">Dados actualizados.</div>
    @endif
    @if (session('password'))
        <div class="alert alert-success">Senha alterada.</div>
    @endif
    @if (session('NoAuth') && session('NoAuth') !== '1')
        <div class="alert alert-danger">{{ session('NoAuth') }}</div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-body">
            <h4 class="mb-1">Editar conta de {{ $user->name }}</h4>

        </div>
    </div>

    {{-- ============ Dados pessoais ============ --}}
    <div class="card shadow mb-4">
        <div class="card-body">
            <h5 class="mb-3">Dados pessoais</h5>

            @if ($errors->updateData->any())
                <div class="alert alert-danger">
                    <ul class="mb-0 pl-3">
                        @foreach ($errors->updateData->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.user.update', $user->uuid) }}">
                @csrf
                @method('PUT')

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="name">Nome</label>
                        <input id="name" type="text" name="name" class="form-control"
                               value="{{ old('name', $user->name) }}" required>
                    </div>

                    <div class="form-group col-md-6">
                        <label for="email">Email</label>
                        <input id="email" type="email" name="email" class="form-control"
                               value="{{ old('email', $user->email) }}" required>
                    </div>
                </div>

                @if ($canSetLevel)
                    <div class="form-group col-md-6 px-0">
                        <label for="level">Nível de acesso</label>
                        <select id="level" name="level" class="form-control" required>
                            @foreach ($levels as $level)
                                <option value="{{ $level }}" {{ old('level', $user->level) === $level ? 'selected' : '' }}>
                                    {{ $level }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <button class="btn btn-primary" type="submit">Salvar alterações</button>
            </form>
        </div>
    </div>

    {{-- ============ Senha ============ --}}
    <div class="card shadow mb-4">
        <div class="card-body">
            <h5 class="mb-1">Alterar senha</h5>
            <p class="text-muted" style="font-size: .8rem;">
                @if ((int) Auth::id() === (int) $user->id)
                    Confirme com a sua senha actual.
                @else
                    Está a repor a senha de outra pessoa. Confirme a operação com a <b>sua</b> senha.
                @endif
            </p>

            @if ($errors->updatePassword->any())
                <div class="alert alert-danger">
                    <ul class="mb-0 pl-3">
                        @foreach ($errors->updatePassword->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.user.password', $user->uuid) }}">
                @csrf
                @method('PUT')

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="current_password">A sua senha actual</label>
                        <input id="current_password" type="password" name="current_password"
                               class="form-control" required autocomplete="current-password">
                    </div>

                    <div class="form-group col-md-4">
                        <label for="password">Nova senha</label>
                        <input id="password" type="password" name="password"
                               class="form-control" required autocomplete="new-password">
                    </div>

                    <div class="form-group col-md-4">
                        <label for="password_confirmation">Confirmar nova senha</label>
                        <input id="password_confirmation" type="password" name="password_confirmation"
                               class="form-control" required autocomplete="new-password">
                    </div>
                </div>

                <button class="btn btn-outline-primary" type="submit">Alterar senha</button>
            </form>
        </div>
    </div>

    <a href="{{ route('admin.user.show', $user->uuid) }}" class="btn btn-link pl-0">Voltar ao perfil</a>

@endsection
