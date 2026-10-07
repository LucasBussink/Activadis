<x-app-layout>
    <h1 class="mb-6 text-2xl font-semibold text-brand-secondary">Nieuwe activiteit</h1>

    <form method="POST" action="{{ route('activities.store') }}" novalidate class="space-y-6 rounded-lg border border-gray-200 bg-white p-6">
        @csrf

        <p class="text-xs text-gray-500">
            Velden met een <span class="text-red-600">*</span> zijn verplicht.
            <span data-draft-hint>Bij een concept mag je velden leeg laten; die worden als "—" getoond.</span>
        </p>

        <div>
            <label for="title" class="block text-sm font-medium text-gray-700">Titel <span class="text-red-600" data-publish-required>*</span></label>
            <input type="text" name="title" id="title" value="{{ old('title') }}"
                   @class(['mt-1 block w-full rounded-md shadow-sm focus:border-brand-primary focus:ring-brand-primary', 'border-gray-300' => ! $errors->has('title'), 'border-red-500' => $errors->has('title')])>
            @error('title')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700">Omschrijving</label>
            <textarea name="description" id="description" rows="3"
                      @class(['mt-1 block w-full rounded-md shadow-sm focus:border-brand-primary focus:ring-brand-primary', 'border-gray-300' => ! $errors->has('description'), 'border-red-500' => $errors->has('description')])>{{ old('description') }}</textarea>
            @error('description')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div>
                <label for="starts_at" class="block text-sm font-medium text-gray-700">Begintijd <span class="text-red-600" data-publish-required>*</span></label>
                <input type="datetime-local" name="starts_at" id="starts_at" value="{{ old('starts_at') }}" data-date-preview
                       @class(['mt-1 block w-full rounded-md shadow-sm focus:border-brand-primary focus:ring-brand-primary', 'border-gray-300' => ! $errors->has('starts_at'), 'border-red-500' => $errors->has('starts_at')])>
                <p class="mt-1 text-sm text-gray-600" data-date-preview-for="starts_at"></p>
                @error('starts_at')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="ends_at" class="block text-sm font-medium text-gray-700">Eindtijd <span class="text-red-600" data-publish-required>*</span></label>
                <input type="datetime-local" name="ends_at" id="ends_at" value="{{ old('ends_at') }}" data-date-preview
                       @class(['mt-1 block w-full rounded-md shadow-sm focus:border-brand-primary focus:ring-brand-primary', 'border-gray-300' => ! $errors->has('ends_at'), 'border-red-500' => $errors->has('ends_at')])>
                <p class="mt-1 text-sm text-gray-600" data-date-preview-for="ends_at"></p>
                <p class="mt-1 text-xs text-gray-500">Kies een latere datum voor een activiteit over meerdere dagen.</p>
                @error('ends_at')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div>
                <label for="signup_deadline" class="block text-sm font-medium text-gray-700">Inschrijven tot <span class="text-red-600" data-publish-required>*</span></label>
                <input type="datetime-local" name="signup_deadline" id="signup_deadline" value="{{ old('signup_deadline') }}" data-date-preview
                       @class(['mt-1 block w-full rounded-md shadow-sm focus:border-brand-primary focus:ring-brand-primary', 'border-gray-300' => ! $errors->has('signup_deadline'), 'border-red-500' => $errors->has('signup_deadline')])>
                <p class="mt-1 text-sm text-gray-600" data-date-preview-for="signup_deadline"></p>
                @error('signup_deadline')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="personal_contribution" class="block text-sm font-medium text-gray-700">Eigen bijdrage</label>
                <input type="text" inputmode="decimal" placeholder="bijv. 12,50, of - voor geen bijdrage" name="personal_contribution" id="personal_contribution" value="{{ old('personal_contribution') }}"
                       @class(['mt-1 block w-full rounded-md shadow-sm focus:border-brand-primary focus:ring-brand-primary', 'border-gray-300' => ! $errors->has('personal_contribution'), 'border-red-500' => $errors->has('personal_contribution')])>
                <p class="mt-1 text-xs text-gray-500">Wordt alleen getoond op de detailpagina.</p>
                @error('personal_contribution')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div>
                <label for="min_participants" class="block text-sm font-medium text-gray-700">Minimaal aantal deelnemers</label>
                <input type="number" min="0" name="min_participants" id="min_participants" value="{{ old('min_participants') }}"
                       @class(['mt-1 block w-full rounded-md shadow-sm focus:border-brand-primary focus:ring-brand-primary', 'border-gray-300' => ! $errors->has('min_participants'), 'border-red-500' => $errors->has('min_participants')])>
                @error('min_participants')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="max_participants" class="block text-sm font-medium text-gray-700">Maximaal aantal deelnemers <span class="text-red-600" data-publish-required>*</span></label>
                <input type="number" min="0" name="max_participants" id="max_participants" value="{{ old('max_participants') }}"
                       @class(['mt-1 block w-full rounded-md shadow-sm focus:border-brand-primary focus:ring-brand-primary', 'border-gray-300' => ! $errors->has('max_participants'), 'border-red-500' => $errors->has('max_participants')])>
                @error('max_participants')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label for="food_description" class="block text-sm font-medium text-gray-700">Omschrijving eten <span class="text-red-600" data-food-required>*</span></label>
            <textarea name="food_description" id="food_description" rows="2"
                      @class(['mt-1 block w-full rounded-md shadow-sm focus:border-brand-primary focus:ring-brand-primary', 'border-gray-300' => ! $errors->has('food_description'), 'border-red-500' => $errors->has('food_description')])>{{ old('food_description') }}</textarea>
            @error('food_description')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="space-y-3">
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="includes_food" id="includes_food" value="1" @checked(old('includes_food'))
                       class="rounded border-gray-300 text-brand-primary focus:ring-brand-primary">
                Inclusief eten
            </label>
            @error('includes_food')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror

            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="allow_partner" value="1" @checked(old('allow_partner'))
                       class="rounded border-gray-300 text-brand-primary focus:ring-brand-primary">
                +1 meenemen
            </label>
            @error('allow_partner')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror

            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="is_external" value="1" @checked(old('is_external'))
                       class="rounded border-gray-300 text-brand-primary focus:ring-brand-primary">
                Externe activiteit (aanmelden zonder account mogelijk)
            </label>
            @error('is_external')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror

            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="hidden" name="publish" value="0">
                <input type="checkbox" name="publish" id="publish" value="1" @checked(old('publish', true))
                       class="rounded border-gray-300 text-brand-primary focus:ring-brand-primary">
                publiceren (uitvinken als het een concept is)
            </label>
            @error('publish')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('activities.index') }}" class="rounded-md px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900">
                Annuleren
            </a>
            <button type="submit" class="rounded-md bg-brand-primary px-4 py-2 text-sm font-medium text-white hover:opacity-90">
                Activiteit aanmaken
            </button>
        </div>
    </form>

    <script>
        // Toon gekozen datums in woorden, bijv. "10 jan 2026 14:00".
        (() => {
            const formatter = new Intl.DateTimeFormat('nl-NL', {
                day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit',
            });
            const startsAt = document.getElementById('starts_at');
            const endsAt = document.getElementById('ends_at');

            const update = (input) => {
                const preview = document.querySelector(`[data-date-preview-for="${input.id}"]`);
                preview.textContent = input.value
                    ? formatter.format(new Date(input.value)).replace(/\./g, '').replace(' om ', ' ')
                    : '';
            };

            document.querySelectorAll('[data-date-preview]').forEach((input) => {
                input.addEventListener('input', () => update(input));
                update(input);
            });

            // Eindtijd kan niet vóór de begintijd liggen.
            const syncMin = () => { endsAt.min = startsAt.value; };
            startsAt.addEventListener('input', syncMin);
            syncMin();

            // Bij een concept mogen velden leeg blijven; alleen bij publiceren zijn ze verplicht.
            const publish = document.getElementById('publish');
            const includesFood = document.getElementById('includes_food');
            const syncRequired = () => {
                document.querySelectorAll('[data-publish-required]').forEach((el) => el.hidden = ! publish.checked);
                document.querySelectorAll('[data-food-required]').forEach((el) => el.hidden = ! (publish.checked && includesFood.checked));
                document.querySelector('[data-draft-hint]').hidden = publish.checked;
            };
            publish.addEventListener('change', syncRequired);
            includesFood.addEventListener('change', syncRequired);
            syncRequired();
        })();
    </script>
</x-app-layout>
