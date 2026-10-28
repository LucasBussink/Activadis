<header class="site-nav">
  <div class="site-nav-inner">
    <a href="{{ auth()->check() ? route('activities.index') : route('external.index') }}" class="brand">
      <img src="{{ asset('images/covadis-logo.png') }}" alt="Covadis" class="brand-logo" width="40" height="40">
      Activadis
    </a>

    @auth
      <button type="button" class="nav-toggle" aria-label="Menu openen" aria-expanded="false" aria-controls="nav-menu" data-nav-toggle>
        <i class="bi bi-list"></i>
      </button>

      <nav class="nav-menu" id="nav-menu" aria-label="Hoofdmenu" data-nav-menu>
        <ul class="nav-links">
          @php $onActivities = request()->routeIs('activities.index', 'activities.show'); @endphp
          <li>
            <a href="{{ route('activities.index') }}" @class(['nav-link', 'active' => $onActivities]) {{ $onActivities ? 'aria-current=page' : '' }}>
              Activiteiten
            </a>
          </li>

          @if (Auth::user()->isAdmin())
            @php $onCreate = request()->routeIs('activities.create'); @endphp
            <li>
              <a href="{{ route('activities.create') }}" @class(['nav-link', 'active' => $onCreate]) {{ $onCreate ? 'aria-current=page' : '' }}>
                Activiteit aanmaken
              </a>
            </li>

            @php $onUsers = request()->routeIs('users.*'); @endphp
            <li>
              <a href="{{ route('users.index') }}" @class(['nav-link', 'active' => $onUsers]) {{ $onUsers ? 'aria-current=page' : '' }}>
                Gebruikers
              </a>
            </li>
          @endif
        </ul>

        <div class="nav-user">
          <span @class(['avatar', 'is-admin' => Auth::user()->isAdmin()])>{{ mb_strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}</span>
          <span class="user-name">{{ Auth::user()->name }}</span>

          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout">
              <i class="bi bi-box-arrow-right"></i> Uitloggen
            </button>
          </form>
        </div>
      </nav>
    @endauth

    @guest
      <a href="{{ route('login') }}" class="nav-login"><i class="bi bi-box-arrow-in-right"></i> Inloggen voor medewerkers</a>
    @endguest
  </div>
</header>

<script>
  // Menu openen en sluiten op een telefoon
  (() => {
    const toggle = document.querySelector('[data-nav-toggle]');
    const menu = document.querySelector('[data-nav-menu]');
    if (! toggle || ! menu) return;

    const setOpen = (open) => {
      menu.classList.toggle('is-open', open);
      toggle.setAttribute('aria-expanded', open);
      toggle.setAttribute('aria-label', open ? 'Menu sluiten' : 'Menu openen');
      toggle.querySelector('i').className = open ? 'bi bi-x-lg' : 'bi bi-list';
    };

    toggle.addEventListener('click', () => setOpen(! menu.classList.contains('is-open')));
    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') setOpen(false);
    });
  })();
</script>
