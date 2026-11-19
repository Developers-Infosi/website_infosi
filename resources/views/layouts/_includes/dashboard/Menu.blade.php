@if (null !== Auth::user())


    <nav class="sidebar sidebar-offcanvas" id="sidebar">
        <ul class="nav">

            <li class="nav-item nav-profile">
                <a href="{{ route('admin.user.show', Auth::User()->uuid) }}" class="nav-link">
                    <div class="profile-image">

                        <div class="dot-indicator bg-success"></div>
                    </div>
                    <div class="text-wrapper my-3">
                        <p class="profile-name">{{ Auth::User()->name }}</p>
                        <p class="designation">{{ Auth::User()->level }}</p>
                    </div>
                </a>
            </li>

            <li class="nav-item nav-category">Areas</li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.home') }}">
                    <i class="menu-icon typcn typcn-document-text"></i>
                    <span class="menu-title">Dashboard</span>
                </a>
            </li>

            <li class="nav-item mb-3">
                <a class="nav-link" href="{{ route('site.declaration.index') }}" target="_blank">
                    <i class="menu-icon typcn typcn-document-text"></i>
                    <span class="menu-title">Traveller Health Declaration</span>
                </a>
            </li>

            @if (Auth::user()->level == 'Técnico' || Auth::user()->level == 'Administrador')
                @if (Auth::user()->level == 'Técnico' || Auth::user()->level == 'Administrador')
                    <li class="nav-item nav-category">Declarações</li>
                    <li class="nav-item mb-1">
                        <a class="nav-link" href="{{ route('admin.declaration.index') }}">
                            <i class="menu-icon typcn typcn-document-text"></i>
                            <span class="menu-title">Declarações de Viagem</span>
                        </a>
                    </li>
                    <li class="nav-item mb-3">
                        <a class="nav-link" href="{{ route('admin.declaration.travelers') }}">
                            <i class="menu-icon typcn typcn-user-outline"></i>
                            <span class="menu-title">Viajantes</span>
                        </a>
                    </li>
                @endif
            @endif


            @if ('Administrador' == Auth::user()->level)
                {{-- Menu Users --}}
                <li class="nav-item nav-category mt-2">Utilizadores</li>
                <li class="nav-item mb-4">
                    <a class="nav-link" href="{{ route('admin.user.index') }}">
                        <i class="menu-icon typcn typcn-document-text"></i>
                        <span class="menu-title">Utilizadores </span>
                    </a>
                </li>
            @endif


        </ul>
    </nav>
@endif
