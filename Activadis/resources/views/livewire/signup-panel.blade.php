<div class="signup-panel">
  {{-- Bezetting --}}
  <div class="capacity">
    <div class="capacity-header">
      <span><i class="bi bi-people"></i> Deelnemers</span>
      <strong>{{ $registered }}{{ $max ? '/' . $max : '' }}</strong>
    </div>

    @if ($max)
      <div @class(['bar', 'full' => $isFull])>
        <span style="width: {{ $percentage }}%"></span>
      </div>
      <p class="hint">
        {{ $isFull ? 'Deze activiteit is vol.' : $spotsLeft . ' ' . ($spotsLeft === 1 ? 'plek' : 'plekken') . ' over' }}
        @if ($activity->min_participants)
          · minimaal {{ $activity->min_participants }} nodig
        @endif
      </p>
    @elseif ($activity->min_participants)
      <p class="hint">Minimaal {{ $activity->min_participants }} nodig</p>
    @endif
  </div>

  {{-- Inschrijven / uitschrijven --}}
  <div class="signup-box {{ $signupState }}">
    @if ($notice)
      <p @class(['notice', 'error' => $noticeIsError]) wire:key="notice-{{ $noticeId }}" role="status">
        <i @class(['bi', 'bi-exclamation-circle-fill' => $noticeIsError, 'bi-check-circle-fill' => ! $noticeIsError])></i>
        {{ $notice }}
      </p>
    @endif

    @if ($signupState === 'signed-up')
      <div class="signup-state">
        <i class="bi bi-check-circle-fill"></i>
        <div>
          <p class="state-title">Je bent ingeschreven</p>
          <p class="state-text">Je plek is gereserveerd. Tot dan!</p>
        </div>
      </div>

      @if ($activity->allow_partner || $activity->includes_food)
        @if ($signupOpen)
          @if ($activity->allow_partner)
            <label class="partner-toggle">
              <input type="checkbox" wire:model.live="bringsPartner">
              <span>Ik neem een +1 mee</span>
            </label>
          @endif

          @if ($activity->includes_food)
            <div class="field">
              <label for="dietary_notes">Dieetwensen of allergieën</label>
              <textarea id="dietary_notes" wire:model.live.debounce.800ms="dietaryNotes" rows="3" maxlength="500" placeholder="Bijvoorbeeld vegetarisch, notenallergie. Ook voor je +1." @class(['has-error' => $errors->has('dietaryNotes')])></textarea>
              @error('dietaryNotes')
                <p class="field-error">{{ $message }}</p>
              @enderror
              <p class="hint">Alleen zichtbaar voor de organisatie.</p>
            </div>
          @endif

          <p class="save-status" aria-live="polite">
            <span wire:loading wire:target="bringsPartner,dietaryNotes" class="saving">Opslaan...</span>
            @if ($saveCount > 0)
              <span wire:loading.remove wire:target="bringsPartner,dietaryNotes" wire:key="saved-{{ $saveCount }}" class="saved"><i class="bi bi-check2"></i> Opgeslagen</span>
            @endif
          </p>
        @else
          @if ($mySignup?->brings_partner)
            <p class="hint"><i class="bi bi-person-plus"></i> Je neemt een +1 mee.</p>
          @endif
          @if ($activity->includes_food && $mySignup?->dietary_notes)
            <p class="hint"><i class="bi bi-cup-hot"></i> Je dieetwensen: {{ $mySignup->dietary_notes }}</p>
          @endif
        @endif
      @endif

      @if ($signupOpen)
        <button type="button" class="btn-secondary" wire:click="signOff" wire:confirm="Weet je zeker dat je je wilt uitschrijven? Je +1 en dieetwensen worden dan ook verwijderd." wire:loading.attr="disabled" wire:target="signOff">
          <i class="bi bi-x-lg"></i> Uitschrijven
        </button>
      @else
        <p class="hint">Uitschrijven kan niet meer, de inschrijving is gesloten.</p>
      @endif
    @elseif ($signupState === 'open')
      @if ($activity->allow_partner)
        <label class="partner-toggle">
          <input type="checkbox" wire:model="bringsPartner">
          <span>Ik neem een +1 mee</span>
        </label>
      @endif

      @if ($activity->includes_food)
        <div class="field">
          <label for="dietary_notes">Dieetwensen of allergieën <span class="optional">(optioneel)</span></label>
          <textarea id="dietary_notes" wire:model="dietaryNotes" rows="3" maxlength="500" placeholder="Bijvoorbeeld vegetarisch, notenallergie. Ook voor je +1." @class(['has-error' => $errors->has('dietaryNotes')])></textarea>
          @error('dietaryNotes')
            <p class="field-error">{{ $message }}</p>
          @enderror
          <p class="hint">Alleen zichtbaar voor de organisatie.</p>
        </div>
      @endif

      <button type="button" class="btn-primary" wire:click="signUp" wire:loading.attr="disabled" wire:target="signUp">
        <i class="bi bi-person-plus"></i> Inschrijven
      </button>

      @if ($deadline)
        <p class="hint">Inschrijven kan tot {{ $deadline->isoFormat('D MMMM, HH:mm') }}</p>
      @endif
    @else
      <div class="signup-state">
        <i @class([
            'bi',
            'bi-pencil-square' => $signupState === 'draft',
            'bi-lock-fill' => $signupState === 'closed',
            'bi-calendar-check' => $signupState === 'ended',
            'bi-x-circle-fill' => $signupState === 'full',
        ])></i>
        <div>
          <p class="state-title">
            {{ match ($signupState) {
                'draft' => 'Nog niet open',
                'ended' => 'Activiteit is geweest',
                'closed' => 'Inschrijving gesloten',
                'full' => 'Activiteit is vol',
            } }}
          </p>
          <p class="state-text">
            {{ match ($signupState) {
                'draft' => 'Dit is een concept. Inschrijven kan na publiceren.',
                'ended' => 'Inschrijven kan niet meer.',
                'closed' => 'De deadline om in te schrijven is verstreken.',
                'full' => 'Alle plekken zijn bezet.',
            } }}
          </p>
        </div>
      </div>
    @endif
  </div>
</div>
