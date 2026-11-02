@extends('layouts.app')

@section('title', __('Collaborations'))

@php
    $collaborationTypes = [
        'community_trip' => __('Community trip'),
        'event' => __('Event or festival'),
        'operator' => __('Travel operator'),
        'corporate' => __('Corporate trip'),
        'media' => __('Content and media'),
        'other' => __('Other'),
    ];
@endphp

@section('content')
    <section class="container-page pt-12 pb-6">
        <p class="text-xs font-semibold tracking-[0.18em] text-brand-500 uppercase">{{ __('Collaborations') }}</p>
        <h1 class="mt-3 max-w-[22ch] text-3xl font-semibold tracking-tight text-balance text-ink md:text-5xl">{{ __("Let's talk partnership") }}</h1>
        <p class="mt-4 max-w-[60ch] text-ink-muted">{{ __('We partner with operators, companies, and tourism businesses to move more people across Indonesia.') }}</p>
    </section>

    <section class="container-page pt-4" aria-labelledby="collab-form-title">
        <div class="grid gap-8 rounded-lg border border-line bg-surface p-5 sm:p-8 lg:grid-cols-[1fr_1.4fr] lg:gap-12 lg:p-10">
            <div class="max-w-sm">
                <h2 id="collab-form-title" class="text-2xl font-semibold tracking-tight text-ink">{{ __('Tell us about your community') }}</h2>
                <p class="mt-2 text-sm leading-relaxed text-ink-muted">{{ __('Fill in the form and our partnership team will reply within two working days.') }}</p>
            </div>

            {{-- Not sent yet: the disabled submit also blocks Enter-key submission --}}
            <form method="post" class="grid gap-4 sm:grid-cols-2" novalidate aria-labelledby="collab-form-title">
                <div>
                    <label for="collab-name" class="form-label">{{ __('Full name') }}</label>
                    <input id="collab-name" name="name" type="text" class="form-input" autocomplete="name" maxlength="100" required>
                </div>
                <div>
                    <label for="collab-email" class="form-label">{{ __('Email') }}</label>
                    <input id="collab-email" name="email" type="email" class="form-input" autocomplete="email" inputmode="email" maxlength="150" required>
                </div>
                <div>
                    <label for="collab-phone" class="form-label">{{ __('Phone number') }} <span class="font-normal text-ink-subtle">{{ __('(optional)') }}</span></label>
                    <input id="collab-phone" name="phone" type="tel" class="form-input" autocomplete="tel" inputmode="tel" maxlength="20" placeholder="08xxxxxxxxxx">
                </div>
                <div>
                    <label for="collab-organization" class="form-label">{{ __('Organization or community name') }}</label>
                    <input id="collab-organization" name="organization" type="text" class="form-input" autocomplete="organization" maxlength="120" required>
                </div>
                <div class="sm:col-span-2">
                    <label for="collab-type" class="form-label">{{ __('Collaboration type') }}</label>
                    <select id="collab-type" name="type" class="form-input appearance-none" required>
                        <option value="">{{ __('Choose one') }}</option>
                        @foreach ($collaborationTypes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label for="collab-message" class="form-label">{{ __('Message') }}</label>
                    <textarea id="collab-message" name="message" rows="5" class="form-input resize-y" maxlength="1000" required
                              aria-describedby="collab-message-hint"></textarea>
                    <p id="collab-message-hint" class="form-hint">{{ __('Tell us your plan, schedule, and estimated number of participants.') }}</p>
                </div>

                <div class="flex flex-col gap-3 sm:col-span-2">
                    <button type="submit" class="btn-primary sm:self-start" disabled aria-describedby="collab-phase-info">
                        <x-icon name="mail" class="size-4" />
                        {{ __('Send') }}
                    </button>
                    <p id="collab-phase-info" class="flex items-start gap-2 rounded-md bg-surface-muted p-3 text-xs text-ink-muted">
                        <x-icon name="info-circle" class="size-4" />
                        <span>{{ __('Online submission opens in the next phase. For now, email us at') }} <a href="mailto:halo@routeindonesia.id" class="font-semibold text-brand-ink underline underline-offset-2">halo@routeindonesia.id</a>.</span>
                    </p>
                </div>
            </form>
        </div>
    </section>
@endsection
