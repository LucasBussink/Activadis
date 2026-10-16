@push('styles')
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  @vite(['resources/css/pages/users.css'])
@endpush

<x-app-layout>
  <div class="users-page">
    <header class="page-header">
      <div>
        <h1>Gebruikers</h1>
        <p class="subtitle">{{ $users->total() }} {{ $users->total() === 1 ? 'gebruiker' : 'gebruikers' }}</p>
      </div>

      <a href="{{ route('users.create') }}" class="btn-primary">
        <i class="bi bi-person-plus"></i> Nieuwe gebruiker
      </a>
    </header>

    @if (session('status'))
      <p class="flash"><i class="bi bi-check-circle-fill"></i> {{ session('status') }}</p>
    @endif
    @if (session('error'))
      <p class="flash error"><i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}</p>
    @endif

    @if ($users->isEmpty())
      <div class="empty-state">
        <i class="bi bi-people"></i>
        <p>Nog geen gebruikers.</p>
      </div>
    @else
      <ul class="user-list">
        @foreach ($users as $user)
          @php
            $invite = $invites[$user->email] ?? null;
            $isMe = $user->id === auth()->id();
          @endphp

          <li class="user-row">
            <span @class(['avatar', 'is-admin' => $user->isAdmin()])>{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>

            <div class="user-info">
              <p class="user-name">
                {{ $user->name }}
                @if ($isMe)
                  <span class="you">jij</span>
                @endif
              </p>
              <p class="user-email">{{ $user->email }}</p>
            </div>

            <div class="user-tags">
              <span @class(['pill', 'admin' => $user->isAdmin()])>{{ $user->isAdmin() ? 'Beheerder' : 'Gebruiker' }}</span>

              @if ($invite === 'pending')
                <span class="pill pending" title="De gebruiker heeft nog geen wachtwoord ingesteld"><i class="bi bi-envelope"></i> Uitnodiging open</span>
              @elseif ($invite === 'expired')
                <span class="pill expired" title="De link in de mail is niet meer geldig"><i class="bi bi-clock-history"></i> Uitnodiging verlopen</span>
              @endif
            </div>

            <div class="user-actions">
              @if ($invite)
                <form method="POST" action="{{ route('users.invite', $user) }}">
                  @csrf
                  <button type="submit" class="icon-button" title="Uitnodiging opnieuw sturen" aria-label="Uitnodiging opnieuw sturen aan {{ $user->name }}">
                    <i class="bi bi-send"></i>
                  </button>
                </form>
              @endif

              @unless ($isMe)
                <button type="button" class="icon-button danger" data-dialog-open="delete-user-{{ $user->id }}" title="Verwijderen" aria-label="{{ $user->name }} verwijderen">
                  <i class="bi bi-trash3"></i>
                </button>
              @endunless
            </div>

            @unless ($isMe)
              <dialog id="delete-user-{{ $user->id }}" class="confirm-dialog" aria-labelledby="delete-title-{{ $user->id }}">
                <form method="POST" action="{{ route('users.destroy', $user) }}">
                  @csrf
                  @method('DELETE')

                  <h2 id="delete-title-{{ $user->id }}">{{ $user->name }} verwijderen?</h2>
                  <p>Het account en alle inschrijvingen van deze gebruiker worden verwijderd. Dit kan niet ongedaan worden gemaakt.</p>

                  <div class="dialog-actions">
                    <button type="button" class="btn-secondary" data-dialog-close>Annuleren</button>
                    <button type="submit" class="btn-danger">Verwijderen</button>
                  </div>
                </form>
              </dialog>
            @endunless
          </li>
        @endforeach
      </ul>

      {{ $users->links() }}
    @endif
  </div>

  <script>
    // Bevestigingsvenster voor verwijderen
    document.querySelectorAll('[data-dialog-open]').forEach((button) => {
      button.addEventListener('click', () => document.getElementById(button.dataset.dialogOpen)?.showModal());
    });
    document.querySelectorAll('[data-dialog-close]').forEach((button) => {
      button.addEventListener('click', () => button.closest('dialog').close());
    });
  </script>
</x-app-layout>
