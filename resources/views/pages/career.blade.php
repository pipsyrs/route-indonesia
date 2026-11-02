@extends('layouts.app')

@section('title', __('Careers'))

@php
    // Sample postings until a jobs source exists.
    $jobs = [
        ['title' => 'Frontend Engineer', 'location' => __('Remote'), 'type' => __('Full-time'), 'desc' => __('Build fast, accessible booking pages with Laravel, Blade, and Tailwind.')],
        ['title' => 'Operations Coordinator', 'location' => 'Bandung', 'type' => __('Full-time'), 'desc' => __('Keep schedules, pickup points, and operator partners running on time every day.')],
        ['title' => 'Customer Support Specialist', 'location' => 'Jakarta', 'type' => __('Contract'), 'desc' => __('Help passengers by chat and phone before, during, and after their trip.')],
    ];
@endphp

@section('content')
    <section class="container-page pt-12 pb-10">
        <p class="text-xs font-semibold tracking-[0.18em] text-brand-500 uppercase">{{ __('Careers') }}</p>
        <h1 class="mt-3 max-w-[22ch] text-3xl font-semibold tracking-tight text-balance text-ink md:text-5xl">{{ __('Help us move Indonesia.') }}</h1>
        <p class="mt-4 max-w-[60ch] text-ink-muted">{{ __('We are a small team building the easiest way to travel between cities.') }}</p>
    </section>

    <section class="container-page py-6" aria-labelledby="career-open-title">
        <h2 id="career-open-title" class="text-2xl font-semibold tracking-tight text-ink">{{ __('Open positions') }}</h2>
        <ul class="mt-6 grid gap-x-10 gap-y-8 border-t border-line pt-6 md:grid-cols-3">
            @foreach ($jobs as $job)
                <li class="flex flex-col">
                    <h3 class="font-semibold text-ink">{{ $job['title'] }}</h3>
                    <p class="mt-1 text-xs font-medium text-ink-subtle">{{ $job['location'] }} · {{ $job['type'] }}</p>
                    <p class="mt-1.5 text-sm leading-relaxed text-ink-muted">{{ $job['desc'] }}</p>
                    <button type="button" aria-haspopup="dialog" data-modal-open="job-apply-modal" data-job-title="{{ $job['title'] }}"
                            class="mt-3 inline-flex items-center gap-1.5 self-start text-sm font-semibold text-brand-ink underline underline-offset-2"
                            aria-label="{{ __('Apply for :title', ['title' => $job['title']]) }}">
                        {{ __('Apply') }}
                        <x-icon name="arrow-right" class="size-4" />
                    </button>
                </li>
            @endforeach
        </ul>
    </section>

    <section class="container-page pt-10" aria-labelledby="career-cv-title">
        <div class="card p-6 sm:p-10">
            <h2 id="career-cv-title" class="text-xl font-semibold text-ink">{{ __("Don't see a role that fits?") }}</h2>
            <p class="mt-2 max-w-[60ch] text-sm text-ink-muted">{{ __('Send your CV anyway and we will reach out when a role fits you.') }}</p>
            <a href="mailto:halo@routeindonesia.id" class="btn-outline mt-6">
                <x-icon name="mail" class="size-4" />
                {{ __('Send your CV') }}
            </a>
        </div>
    </section>

    <x-modal id="job-apply-modal" :title="__('Job application')" class="max-w-lg" data-module="job-apply">
        {{-- Not sent yet: the disabled submit also blocks Enter-key submission --}}
        <form method="post" class="grid gap-4" novalidate aria-labelledby="job-apply-modal-title">
            <div>
                <label for="apply-position" class="form-label">{{ __('Position') }}</label>
                <select id="apply-position" name="position" class="form-input appearance-none" required data-job-select>
                    @foreach ($jobs as $job)
                        <option value="{{ $job['title'] }}">{{ $job['title'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="apply-name" class="form-label">{{ __('Full name') }}</label>
                <input id="apply-name" name="name" type="text" class="form-input" autocomplete="name" maxlength="100" required>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="apply-email" class="form-label">{{ __('Email') }}</label>
                    <input id="apply-email" name="email" type="email" class="form-input" autocomplete="email" inputmode="email" maxlength="150" required>
                </div>
                <div>
                    <label for="apply-phone" class="form-label">{{ __('Phone number') }}</label>
                    <input id="apply-phone" name="phone" type="tel" class="form-input" autocomplete="tel" inputmode="tel" maxlength="20" placeholder="08xxxxxxxxxx" required>
                </div>
            </div>
            <div>
                <label for="apply-cv" class="form-label">CV</label>
                <input id="apply-cv" name="cv" type="file" class="form-input file:mr-3 file:rounded file:border-0 file:bg-brand-soft file:px-3 file:py-1 file:text-sm file:font-semibold file:text-brand-ink"
                       accept="application/pdf,.pdf" required aria-describedby="apply-cv-hint">
                <p id="apply-cv-hint" class="form-hint">{{ __('PDF, max 2 MB.') }}</p>
            </div>
            <div>
                <label for="apply-portfolio" class="form-label">{{ __('LinkedIn or portfolio link') }} <span class="font-normal text-ink-subtle">{{ __('(optional)') }}</span></label>
                <input id="apply-portfolio" name="portfolio" type="url" class="form-input" inputmode="url" maxlength="200" placeholder="https://">
            </div>
            <div>
                <label for="apply-message" class="form-label">{{ __('Short introduction') }}</label>
                <textarea id="apply-message" name="message" rows="4" class="form-input resize-y" maxlength="1000" required></textarea>
            </div>

            <button type="submit" class="btn-primary w-fit px-6" disabled aria-describedby="apply-phase-info">
                {{ __('Submit application') }}
            </button>
            <p id="apply-phase-info" class="flex items-start gap-2 rounded-md bg-surface-muted p-3 text-xs text-ink-muted">
                <x-icon name="info-circle" class="size-4" />
                <span>{{ __('Online submission opens in the next phase. For now, email us at') }} <a href="mailto:halo@routeindonesia.id" class="font-semibold text-brand-ink underline underline-offset-2">halo@routeindonesia.id</a>.</span>
            </p>
        </form>
    </x-modal>
@endsection
