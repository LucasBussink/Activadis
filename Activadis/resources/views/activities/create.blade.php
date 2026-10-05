<x-app-layout>
  <h1 class="mb-6 text-2xl font-semibold text-brand-secondary">Nieuwe activiteit</h1>

  @if ($errors->any())
    <div class="mb-6 rounded-md bg-red-50 px-4 py-3 text-sm text-red-800">
      <ul class="list-inside list-disc">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('activities.store') }}"
    class="space-y-6 rounded-lg border border-gray-200 bg-white p-6">
    @csrf

    <div>
      <label for="title" class="block text-sm font-medium text-gray-700">Titel</label>
      <input type="text" name="title" id="title" value="{{ old('title') }}" required
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary">
    </div>

    <div>
      <label for="description" class="block text-sm font-medium text-gray-700">Omschrijving</label>
      <textarea name="description" id="description" rows="3"
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary">{{ old('description') }}</textarea>
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
      <div>
        <label for="starts_at" class="block text-sm font-medium text-gray-700">Datum &amp; tijd</label>
        <input type="datetime-local" name="starts_at" id="starts_at" value="{{ old('starts_at') }}" required
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary">
      </div>

      <div>
        <label for="signup_deadline" class="block text-sm font-medium text-gray-700">Inschrijven tot</label>
        <input type="datetime-local" name="signup_deadline" id="signup_deadline" value="{{ old('signup_deadline') }}"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary">
      </div>
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
      <div>
        <label for="cost" class="block text-sm font-medium text-gray-700">Kosten (informatief)</label>
        <input type="number" step="0.01" min="0" name="cost" id="cost" value="{{ old('cost') }}"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary">
      </div>

      <div>
        <label for="personal_contribution" class="block text-sm font-medium text-gray-700">Eigen bijdrage</label>
        <input type="number" step="0.01" min="0" name="personal_contribution" id="personal_contribution"
          value="{{ old('personal_contribution') }}"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary">
        <p class="mt-1 text-xs text-gray-500">Wordt alleen getoond op de detailpagina.</p>
      </div>
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
      <div>
        <label for="min_participants" class="block text-sm font-medium text-gray-700">Minimaal aantal deelnemers</label>
        <input type="number" min="0" name="min_participants" id="min_participants"
          value="{{ old('min_participants') }}"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary">
      </div>

      <div>
        <label for="max_participants" class="block text-sm font-medium text-gray-700">Maximaal aantal deelnemers</label>
        <input type="number" min="0" name="max_participants" id="max_participants"
          value="{{ old('max_participants') }}"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary">
      </div>
    </div>

    <div>
      <label for="food_description" class="block text-sm font-medium text-gray-700">Omschrijving eten</label>
      <textarea name="food_description" id="food_description" rows="2"
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary">{{ old('food_description') }}</textarea>
    </div>

    <div class="space-y-3">
      <label class="flex items-center gap-2 text-sm text-gray-700">
        <input type="checkbox" name="includes_food" value="1" @checked(old('includes_food'))
          class="rounded border-gray-300 text-brand-primary focus:ring-brand-primary">
        Inclusief eten
      </label>

      <label class="flex items-center gap-2 text-sm text-gray-700">
        <input type="checkbox" name="allow_partner" value="1" @checked(old('allow_partner'))
          class="rounded border-gray-300 text-brand-primary focus:ring-brand-primary">
        Partner toestaan
      </label>

      <label class="flex items-center gap-2 text-sm text-gray-700">
        <input type="checkbox" name="is_external" value="1" @checked(old('is_external'))
          class="rounded border-gray-300 text-brand-primary focus:ring-brand-primary">
        Externe activiteit (aanmelden zonder account mogelijk)
      </label>
    </div>

    <div>
      <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
      <select name="status" id="status"
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary">
        <option value="draft" @selected(old('status', 'draft') === 'draft')>Concept</option>
        <option value="published" @selected(old('status') === 'published')>Gepubliceerd</option>
      </select>
    </div>

    <div class="flex justify-end gap-3">
      <a href="{{ route('activities.index') }}"
        class="rounded-md px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900">
        Annuleren
      </a>
      <button type="submit"
        class="rounded-md bg-brand-primary px-4 py-2 text-sm font-medium text-white hover:opacity-90">
        Activiteit aanmaken
      </button>
    </div>
  </form>
</x-app-layout>
