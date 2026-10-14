@php
  $isEdit = (bool) $activity;
  $formatForInput = fn (?\Carbon\CarbonInterface $date) => $date?->format('Y-m-d\TH:i');

  $contributionValue = null;
  if ($activity && $activity->personal_contribution !== null) {
      $contributionValue = (float) $activity->personal_contribution === 0.0
          ? '-'
          : rtrim(rtrim(number_format((float) $activity->personal_contribution, 2, ',', ''), '0'), ',');
  }

  $backRoute = $isEdit ? route('activities.show', $activity) : route('activities.index');
  $formAction = $isEdit ? route('activities.update', $activity) : route('activities.store');
  $existingImages = $activity?->images ?? collect();
@endphp

@push('styles')
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  @vite(['resources/css/pages/activity-form.css'])
@endpush

<x-app-layout>
  <div class="activity-form-page">
    <header class="page-header">
      <a href="{{ $backRoute }}" class="back-link">
        <i class="bi bi-arrow-left"></i> {{ $isEdit ? 'Terug naar activiteit' : 'Terug naar overzicht' }}
      </a>
      <h1>{{ $isEdit ? 'Activiteit bewerken' : 'Nieuwe activiteit' }}</h1>
      <p class="subtitle">
        Velden met een <span class="required">*</span> zijn verplicht.
        <span data-draft-hint>Bij een concept mag je velden leeg laten; die worden als een streepje getoond.</span>
      </p>
    </header>

    <form method="POST" action="{{ $formAction }}" enctype="multipart/form-data" novalidate>
      @csrf
      @if ($isEdit)
        @method('PUT')
      @endif

      <div class="form-columns">
      <div class="form-column">

      {{-- Algemeen --}}
      <section class="form-section">
        <h2>Algemeen</h2>

        <div @class(['field', 'has-error' => $errors->has('title')])>
          <label for="title">Titel <span class="required" data-publish-required>*</span></label>
          <input type="text" name="title" id="title" value="{{ old('title', $activity?->title) }}">
          @error('title') <p class="error">{{ $message }}</p> @enderror
        </div>

        <div @class(['field', 'has-error' => $errors->has('description')])>
          <label for="description">Omschrijving</label>
          <textarea name="description" id="description" rows="4">{{ old('description', $activity?->description) }}</textarea>
          @error('description') <p class="error">{{ $message }}</p> @enderror
        </div>
      </section>

      {{-- Foto's --}}
      <section class="form-section">
        <h2>Foto's</h2>
        <p class="section-hint">
          Maximaal 5 foto's (JPG, PNG of WebP, elk tot 5 MB). De eerste foto is de omslagfoto op het kaartje.
        </p>

        @if ($existingImages->isNotEmpty())
          <ul class="photo-previews" data-existing-previews>
            @foreach ($existingImages as $image)
              <li class="preview" data-existing-preview data-image-id="{{ $image->id }}">
                <img src="{{ $image->url() }}" alt="">
                <span class="cover-badge" data-cover-badge @if (! $loop->first) hidden @endif>Omslag</span>
                <label class="remove" title="Verwijderen">
                  <input type="checkbox" name="remove_images[]" value="{{ $image->id }}" data-remove-existing>
                  <i class="bi bi-x-lg"></i>
                </label>
              </li>
            @endforeach
          </ul>
        @endif

        <div @class(['dropzone', 'has-error' => $errors->has('images') || $errors->has('images.*')]) data-dropzone>
          <input type="file" name="images[]" id="images" accept="image/jpeg,image/png,image/webp" multiple>
          <i class="bi bi-cloud-arrow-up"></i>
          <p><strong>{{ $isEdit ? "Foto's toevoegen" : "Kies foto's" }}</strong> of sleep ze hierheen</p>
        </div>

        <p class="error" data-photo-message></p>
        @foreach (collect($errors->get('images'))->merge(collect($errors->get('images.*')))->flatten()->unique() as $message)
          <p class="error">{{ $message }}</p>
        @endforeach

        <ul class="photo-previews" data-previews></ul>

        @if ($errors->any())
          <p class="hint">Na een foutmelding moet je nieuwe foto's opnieuw kiezen.</p>
        @endif
      </section>

      </div>
      <div class="form-column">

      {{-- Datum en tijd --}}
      <section class="form-section">
        <h2>Datum en tijd</h2>

        <div class="row stacked">
          <div @class(['field', 'has-error' => $errors->has('starts_at')])>
            <label for="starts_at">Begintijd <span class="required" data-publish-required>*</span></label>
            <input type="datetime-local" name="starts_at" id="starts_at" value="{{ old('starts_at', $formatForInput($activity?->starts_at)) }}" data-date-preview>
            <p class="preview-text" data-date-preview-for="starts_at"></p>
            @error('starts_at') <p class="error">{{ $message }}</p> @enderror
          </div>

          <div @class(['field', 'has-error' => $errors->has('ends_at')])>
            <label for="ends_at">Eindtijd <span class="required" data-publish-required>*</span></label>
            <input type="datetime-local" name="ends_at" id="ends_at" value="{{ old('ends_at', $formatForInput($activity?->ends_at)) }}" data-date-preview>
            <p class="preview-text" data-date-preview-for="ends_at"></p>
            <p class="hint">Kies een latere datum voor een activiteit over meerdere dagen.</p>
            @error('ends_at') <p class="error">{{ $message }}</p> @enderror
          </div>
        </div>

        <div @class(['field', 'has-error' => $errors->has('signup_deadline')])>
          <label for="signup_deadline">Inschrijven tot <span class="required" data-publish-required>*</span></label>
          <input type="datetime-local" name="signup_deadline" id="signup_deadline" value="{{ old('signup_deadline', $formatForInput($activity?->signup_deadline)) }}" data-date-preview>
          <p class="preview-text" data-date-preview-for="signup_deadline"></p>
          @error('signup_deadline') <p class="error">{{ $message }}</p> @enderror
        </div>
      </section>

      {{-- Deelnemers en kosten --}}
      <section class="form-section">
        <h2>Deelnemers en kosten</h2>

        <div class="row">
          <div @class(['field', 'has-error' => $errors->has('min_participants')])>
            <label for="min_participants">Minimaal aantal deelnemers</label>
            <input type="number" min="0" name="min_participants" id="min_participants" value="{{ old('min_participants', $activity?->min_participants) }}">
            @error('min_participants') <p class="error">{{ $message }}</p> @enderror
          </div>

          <div @class(['field', 'has-error' => $errors->has('max_participants')])>
            <label for="max_participants">Maximaal aantal deelnemers <span class="required" data-publish-required>*</span></label>
            <input type="number" min="0" name="max_participants" id="max_participants" value="{{ old('max_participants', $activity?->max_participants) }}">
            @error('max_participants') <p class="error">{{ $message }}</p> @enderror
          </div>
        </div>

        <div @class(['field', 'has-error' => $errors->has('personal_contribution')])>
          <label for="personal_contribution">Eigen bijdrage</label>
          <input type="text" inputmode="decimal" name="personal_contribution" id="personal_contribution"
                 placeholder="bijv. 12,50 of - voor geen bijdrage" value="{{ old('personal_contribution', $contributionValue) }}">
          <p class="hint">Wordt alleen getoond op de detailpagina.</p>
          @error('personal_contribution') <p class="error">{{ $message }}</p> @enderror
        </div>
      </section>

      </div>
      </div>

      {{-- Opties --}}
      <section class="form-section">
        <h2>Opties</h2>

        <div class="checks">
          <label class="check">
            <input type="checkbox" name="includes_food" id="includes_food" value="1" @checked(old('includes_food', $activity?->includes_food))>
            Inclusief eten
          </label>

          <label class="check">
            <input type="checkbox" name="allow_partner" value="1" @checked(old('allow_partner', $activity?->allow_partner))>
            +1 meenemen
          </label>

          <label class="check">
            <input type="checkbox" name="is_external" value="1" @checked(old('is_external', $activity?->is_external))>
            Externe activiteit
          </label>
        </div>

        <div @class(['field', 'has-error' => $errors->has('food_description')]) data-food-field>
          <label for="food_description">Omschrijving eten <span class="required" data-food-required>*</span></label>
          <textarea name="food_description" id="food_description" rows="2">{{ old('food_description', $activity?->food_description) }}</textarea>
          @error('food_description') <p class="error">{{ $message }}</p> @enderror
        </div>
      </section>

      {{-- Publiceren --}}
      <div class="form-actions">
        {{-- Schuifknop: aan = direct publiceren, uit = opslaan als concept --}}
        <div class="publish-toggle">
          <input type="hidden" name="publish" value="0">
          <label class="switch">
            <input type="checkbox" name="publish" id="publish" value="1" @checked(old('publish', $isEdit ? $activity->status === \App\Models\Activity::STATUS_PUBLISHED : true))>
            <span class="switch-label">
              <span class="on">Direct publiceren</span>
              <span class="off">Opslaan als concept</span>
            </span>
          </label>
          @error('publish') <p class="error">{{ $message }}</p> @enderror
        </div>

        <div class="form-buttons">
          <a href="{{ $backRoute }}" class="btn-secondary">Annuleren</a>
          <button type="submit" class="btn-primary" data-submit
                  data-label-publish="{{ $isEdit ? 'Wijzigingen opslaan' : 'Activiteit aanmaken' }}"
                  data-label-draft="Concept opslaan">
            {{ $isEdit ? 'Wijzigingen opslaan' : 'Activiteit aanmaken' }}
          </button>
        </div>
      </div>
    </form>
  </div>

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
      const submitButton = document.querySelector('[data-submit]');
      const syncRequired = () => {
        document.querySelectorAll('[data-publish-required]').forEach((el) => el.hidden = ! publish.checked);
        document.querySelectorAll('[data-food-required]').forEach((el) => el.hidden = ! (publish.checked && includesFood.checked));
        document.querySelector('[data-food-field]').hidden = ! includesFood.checked;
        document.querySelector('[data-draft-hint]').hidden = publish.checked;
        submitButton.textContent = publish.checked ? submitButton.dataset.labelPublish : submitButton.dataset.labelDraft;
      };
      publish.addEventListener('change', syncRequired);
      includesFood.addEventListener('change', syncRequired);
      syncRequired();
    })();

    // Bestaande foto's markeren om te verwijderen (het label schakelt de checkbox al om)
    // en de omslagfoto-badge verplaatsen naar de eerste foto die overblijft.
    (() => {
      const existing = [...document.querySelectorAll('[data-existing-preview]')];

      const syncCoverBadge = () => {
        const firstRemaining = existing.find((item) => ! item.classList.contains('marked-removed'));
        existing.forEach((item) => {
          item.querySelector('[data-cover-badge]').hidden = item !== firstRemaining;
        });
        document.dispatchEvent(new CustomEvent('existing-photos:change'));
      };

      existing.forEach((item) => {
        const checkbox = item.querySelector('[data-remove-existing]');
        checkbox.addEventListener('change', () => {
          item.classList.toggle('marked-removed', checkbox.checked);
          syncCoverBadge();
        });
      });
    })();

    // Foto's kiezen, voorvertonen, verwijderen en een omslagfoto aanwijzen.
    (() => {
      const MAX_FILES = 5;
      const MAX_SIZE = 5 * 1024 * 1024;
      const TYPES = ['image/jpeg', 'image/png', 'image/webp'];

      const dropzone = document.querySelector('[data-dropzone]');
      const input = document.getElementById('images');
      const list = document.querySelector('[data-previews]');
      const message = document.querySelector('[data-photo-message]');
      let files = [];

      // Hoeveel bestaande foto's (nog) niet verwijderd zijn, telt mee voor het maximum.
      const remainingExisting = () => document.querySelectorAll('[data-existing-preview]:not(.marked-removed)').length;

      // Zet onze lijst terug in het echte bestandsveld, zodat het formulier ze meestuurt.
      const syncInput = () => {
        const transfer = new DataTransfer();
        files.forEach((file) => transfer.items.add(file));
        input.files = transfer.files;
      };

      const render = () => {
        list.querySelectorAll('img').forEach((img) => URL.revokeObjectURL(img.src));
        list.innerHTML = '';

        files.forEach((file, index) => {
          const item = document.createElement('li');
          item.className = 'preview';
          item.innerHTML = `
            <img alt="">
            ${index === 0 && remainingExisting() === 0
              ? '<span class="cover-badge">Omslag</span>'
              : `<button type="button" class="make-cover" data-action="cover" data-index="${index}" title="Maak omslagfoto"><i class="bi bi-star"></i></button>`}
            <button type="button" class="remove" data-action="remove" data-index="${index}" title="Verwijderen"><i class="bi bi-x-lg"></i></button>
          `;
          const img = item.querySelector('img');
          img.src = URL.createObjectURL(file);
          img.alt = file.name;
          list.append(item);
        });

        dropzone.classList.toggle('has-files', files.length > 0);
        syncInput();
      };

      document.addEventListener('existing-photos:change', render);

      input.addEventListener('change', () => {
        const problems = new Set();
        const maxNew = Math.max(MAX_FILES - remainingExisting(), 0);

        [...input.files].forEach((file) => {
          if (! TYPES.includes(file.type)) {
            problems.add(`${file.name} is geen JPG, PNG of WebP.`);
          } else if (file.size > MAX_SIZE) {
            problems.add(`${file.name} is groter dan 5 MB.`);
          } else if (files.length >= maxNew) {
            problems.add(`Je kunt in totaal maximaal ${MAX_FILES} foto's hebben.`);
          } else if (! files.some((f) => f.name === file.name && f.size === file.size)) {
            files.push(file);
          }
        });

        message.textContent = [...problems].join(' ');
        render();
      });

      list.addEventListener('click', (event) => {
        const button = event.target.closest('[data-action]');
        if (! button) return;

        const index = Number(button.dataset.index);
        if (button.dataset.action === 'remove') {
          files.splice(index, 1);
        } else {
          // Verplaats de gekozen foto naar voren, die wordt de omslagfoto.
          files.unshift(files.splice(index, 1)[0]);
        }
        message.textContent = '';
        render();
      });

      // Visuele feedback tijdens slepen (het slepen zelf regelt het bestandsveld).
      ['dragenter', 'dragover'].forEach((name) =>
        dropzone.addEventListener(name, () => dropzone.classList.add('is-dragging')));
      ['dragleave', 'drop'].forEach((name) =>
        dropzone.addEventListener(name, () => dropzone.classList.remove('is-dragging')));
    })();
  </script>
</x-app-layout>
