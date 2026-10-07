<nav class="bg-white border-b border-gray-100">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between h-16">
      <div class="flex">
        <!-- Logo -->
        <div class="shrink-0 flex items-center">
          <a href="{{ route('activities.index') }}">
            <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
          </a>
        </div>

        <!-- Navigatie -->
        @auth
          <div class="-my-px ms-6 flex space-x-6 sm:ms-10 sm:space-x-8">
            <x-nav-link :href="route('activities.index')" :active="request()->routeIs('activities.index', 'activities.show')">
              Activiteiten
            </x-nav-link>
            @if (Auth::user()->isAdmin())
              <x-nav-link :href="route('activities.create')" :active="request()->routeIs('activities.create')">
                Activiteit aanmaken
              </x-nav-link>
            @endif
          </div>
        @endauth
      </div>

      <!-- Gebruiker -->
      @auth
        <div class="flex items-center gap-4">
          <span class="text-sm font-medium text-gray-700">{{ Auth::user()->name }}</span>

          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">
              Uitloggen
            </button>
          </form>
        </div>
      @endauth
    </div>
  </div>
</nav>
