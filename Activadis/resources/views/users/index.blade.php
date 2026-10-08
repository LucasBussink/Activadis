<x-app-layout>
    <div>
        <div>
            <a href="{{ route('users.create') }}">
                Nieuwe gebruiker
            </a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Naam</th>
                    <th>E-mailadres</th>
                    <th>Rol</th>
                    <th>Acties</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->isAdmin() ? 'Beheerder' : 'Gebruiker' }}</td>
                        <td>
                            @if ($user->id !== auth()->id())
                                <x-danger-button
                                    x-data=""
                                    x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion-{{ $user->id }}')"
                                >
                                    Verwijderen
                                </x-danger-button>

                                <x-modal name="confirm-user-deletion-{{ $user->id }}" focusable>
                                    <form method="POST" action="{{ route('users.destroy', $user) }}" class="p-6">
                                        @csrf
                                        @method('DELETE')

                                        <h2 class="text-lg font-medium text-gray-900">
                                            Weet je zeker dat je {{ $user->name }} wilt verwijderen?
                                        </h2>
                                        <p class="mt-1 text-sm text-gray-600">
                                            Dit kan niet ongedaan worden gemaakt.
                                        </p>

                                        <div class="mt-6 flex justify-end gap-3">
                                            <x-secondary-button x-on:click="$dispatch('close')">
                                                Annuleren
                                            </x-secondary-button>
                                            <x-danger-button>
                                                Verwijderen
                                            </x-danger-button>
                                        </div>
                                    </form>
                                </x-modal>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">Geen gebruikers gevonden.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{ $users->links() }}
    </div>
</x-app-layout>