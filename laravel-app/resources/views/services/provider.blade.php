@php
    $pageTitle = $providerName . ' Service Profile';
    $prefillName = old('contact_name', $user?->full_name ?? '');
    $prefillEmail = old('contact_email', $user?->email ?? '');
    $prefillPhone = old('contact_phone', $user?->phone ?? '');
    $providerServiceType = (string) ($provider->service_category ?? '');
    if (! array_key_exists($providerServiceType, $serviceTypes)) {
        $providerServiceType = (string) (array_key_first($serviceTypes) ?? '');
    }
    $ratingAvg = $provider->provider_rating_avg !== null ? (float) $provider->provider_rating_avg : 0.0;
    $ratingCount = (int) ($provider->provider_rating_count ?? 0);
    $displayRating = $ratingCount > 0 ? number_format($ratingAvg, 1) : '0.0';
    $fullStars = (int) round($ratingAvg);
    $galleryProjects = $portfolioProjects
        ->filter(fn ($project) => trim((string) ($project->image_path ?? '')) !== '')
        ->values();
@endphp
@extends('layouts.app')

@section('meta_description', 'View construction service provider details, ratings, previous work photos, and request this provider on NaijaBuilders.')

@section('content')
<div class="container service-profile-page page-shell">
    <style>
        .service-profile-page {
            padding-top: var(--spacing-lg);
            padding-bottom: var(--spacing-xl);
        }

        .service-profile-breadcrumb {
            margin-bottom: var(--spacing-md);
            color: var(--neutral-600);
            font-size: var(--font-size-sm);
        }

        .service-profile-breadcrumb a {
            color: var(--primary-color);
        }

        .service-profile-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.35fr) minmax(300px, 0.8fr);
            gap: var(--spacing-lg);
            align-items: start;
        }

        .service-profile-main {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-lg);
        }

        .service-profile-side {
            position: sticky;
            top: 1rem;
        }

        .service-profile-hero {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: var(--spacing-lg);
            align-items: start;
        }

        .service-profile-hero h1 {
            margin: 0 0 0.55rem;
            font-size: clamp(1.55rem, 3vw, 2.25rem);
        }

        .service-profile-meta {
            display: flex;
            gap: 0.45rem;
            flex-wrap: wrap;
            color: var(--neutral-600);
            font-size: var(--font-size-sm);
        }

        .service-chip {
            display: inline-flex;
            align-items: center;
            padding: 0.22rem 0.6rem;
            border-radius: var(--rounded-full);
            background: var(--primary-light);
            color: var(--primary-dark);
            font-size: var(--font-size-xs);
            font-weight: var(--font-weight-bold);
        }

        .service-rating {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            color: var(--neutral-600);
            font-size: var(--font-size-sm);
            font-weight: var(--font-weight-semibold);
        }

        .service-stars {
            display: inline-flex;
            gap: 0.08rem;
            color: var(--accent-warning);
            line-height: 1;
        }

        .service-stars .is-empty {
            color: var(--neutral-300);
        }

        .service-profile-avatar {
            width: 88px;
            height: 88px;
            border-radius: var(--rounded-full);
            border: 3px solid var(--white);
            background: linear-gradient(135deg, var(--secondary-light), var(--primary-light));
            box-shadow: var(--shadow-md);
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            font-size: 1.45rem;
            font-weight: var(--font-weight-bold);
        }

        .service-profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .service-gallery-stage {
            position: relative;
            aspect-ratio: 16 / 9;
            border-radius: var(--rounded-lg);
            overflow: hidden;
            background: var(--neutral-100);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .service-gallery-stage img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .service-gallery-empty {
            width: 100%;
            height: 100%;
            min-height: 260px;
            display: flex;
            align-items: flex-end;
            padding: var(--spacing-lg);
            color: var(--white);
            background:
                linear-gradient(180deg, rgba(9, 31, 67, 0.12), rgba(9, 31, 67, 0.86)),
                url("{{ asset('assets/images/home-suppliers.jpg') }}") center/cover;
        }

        .service-gallery-empty p {
            max-width: 52ch;
            margin: 0;
            color: rgba(255, 255, 255, 0.92);
            font-weight: var(--font-weight-semibold);
        }

        .service-gallery-button {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 36px;
            height: 36px;
            border: 0;
            border-radius: var(--rounded-full);
            background: rgba(9, 31, 67, 0.78);
            color: var(--white);
            cursor: pointer;
            z-index: 2;
        }

        .service-gallery-button--prev {
            left: 0.75rem;
        }

        .service-gallery-button--next {
            right: 0.75rem;
        }

        .service-gallery-thumbs {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.55rem;
            margin-top: 0.65rem;
        }

        .service-gallery-thumb {
            border: 2px solid transparent;
            border-radius: var(--rounded-md);
            overflow: hidden;
            aspect-ratio: 4 / 3;
            background: var(--neutral-100);
            cursor: pointer;
            padding: 0;
        }

        .service-gallery-thumb.is-active {
            border-color: var(--primary-color);
        }

        .service-gallery-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .service-detail-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: var(--spacing-sm);
        }

        .service-detail-item {
            border: 1px solid var(--neutral-200);
            border-radius: var(--rounded-md);
            background: var(--neutral-50);
            padding: 0.75rem;
        }

        .service-detail-item span {
            display: block;
            color: var(--neutral-600);
            font-size: var(--font-size-xs);
            font-weight: var(--font-weight-bold);
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .service-detail-item strong {
            display: block;
            margin-top: 0.25rem;
            color: var(--neutral-900);
        }

        .service-project-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: var(--spacing-md);
        }

        .service-project {
            border: 1px solid var(--neutral-200);
            border-radius: var(--rounded-md);
            overflow: hidden;
            background: var(--white);
        }

        .service-project__media {
            aspect-ratio: 16 / 10;
            background: var(--neutral-100);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--neutral-500);
            overflow: hidden;
        }

        .service-project__media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .service-project__body {
            padding: 0.85rem;
        }

        .service-project__body h3 {
            margin: 0 0 0.4rem;
            font-size: 1rem;
        }

        .service-project__body p {
            margin: 0.4rem 0 0;
            color: var(--neutral-600);
            font-size: var(--font-size-sm);
        }

        .service-status {
            display: inline-flex;
            align-items: center;
            padding: 0.16rem 0.48rem;
            border-radius: var(--rounded-full);
            font-size: var(--font-size-xs);
            font-weight: var(--font-weight-bold);
            text-transform: uppercase;
        }

        .service-status--ongoing {
            background: rgba(52, 152, 219, 0.14);
            color: #1d4ed8;
        }

        .service-status--completed {
            background: rgba(39, 174, 96, 0.14);
            color: #047857;
        }

        .service-review-list {
            display: grid;
            gap: var(--spacing-sm);
        }

        .service-review {
            border: 1px solid var(--neutral-200);
            border-radius: var(--rounded-md);
            padding: 0.8rem;
            background: var(--white);
        }

        .service-review__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--spacing-sm);
        }

        .service-review h4 {
            margin: 0;
            font-size: 0.96rem;
        }

        .service-review p {
            margin: 0.4rem 0 0;
            color: var(--neutral-700);
        }

        .service-review time {
            display: block;
            margin-top: 0.35rem;
            color: var(--neutral-500);
            font-size: var(--font-size-xs);
        }

        .service-empty {
            border: 1px dashed var(--neutral-300);
            border-radius: var(--rounded-md);
            padding: 1rem;
            background: var(--neutral-50);
            color: var(--neutral-600);
        }

        @media (max-width: 1023px) {
            .service-profile-grid {
                grid-template-columns: 1fr;
            }

            .service-profile-side {
                position: static;
            }
        }

        @media (max-width: 680px) {
            .service-profile-hero {
                grid-template-columns: 1fr;
            }

            .service-detail-grid,
            .service-gallery-thumbs {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>

    @if ($successCode === 'requested')
        <div class="alert alert-success" style="margin-bottom: 1rem;">
            Your request for {{ $providerName }} has been received. NaijaBuilders will follow up with the next step.
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger" style="margin-bottom: 1rem;">
            Please check the request form and complete the highlighted details.
        </div>
    @endif

    <div class="service-profile-breadcrumb">
        <a href="/index.php">Home</a> /
        <a href="/hire-service.php">Hire a Service</a> /
        <span>{{ $providerName }}</span>
    </div>

    <div class="service-profile-grid">
        <section class="service-profile-main">
            <div class="card">
                <div class="card-body service-profile-hero">
                    <div>
                        <span class="service-chip">{{ $serviceTypeLabel }}</span>
                        <h1>{{ $providerName }}</h1>
                        <div class="service-profile-meta">
                            <span>{{ $provider->location ?: 'Nigeria' }}</span>
                            @if (trim((string) ($provider->service_areas ?? '')) !== '')
                                <span>Serves {{ $provider->service_areas }}</span>
                            @endif
                            @if ((int) ($provider->is_verified_badge ?? 0) === 1)
                                <span>Verified provider</span>
                            @endif
                        </div>
                        <div class="service-rating" style="margin-top: 0.75rem;">
                            <span class="service-stars" aria-hidden="true">
                                @for ($star = 1; $star <= 5; $star++)
                                    <span class="{{ $star <= $fullStars ? '' : 'is-empty' }}">&#9733;</span>
                                @endfor
                            </span>
                            <span>{{ $displayRating }}/5 ({{ number_format($ratingCount) }} reviews)</span>
                        </div>
                    </div>

                    @php
                        $profileImagePath = trim((string) ($provider->profile_image_path ?? ''));
                        $profileImageUrl = $profileImagePath !== '' ? '/' . ltrim($profileImagePath, '/') : '';
                        $nameParts = preg_split('/\s+/', trim($providerName)) ?: [];
                        $initials = strtoupper(substr($nameParts[0] ?? 'N', 0, 1) . substr($nameParts[1] ?? 'B', 0, 1));
                    @endphp
                    <div class="service-profile-avatar" aria-hidden="true">
                        @if ($profileImageUrl !== '')
                            <img src="{{ $profileImageUrl }}" alt="">
                        @else
                            {{ $initials }}
                        @endif
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h2 style="margin-top: 0;">Work Photos</h2>
                    <div class="service-gallery-stage" id="service-gallery-stage">
                        @if ($galleryProjects->count() > 0)
                            @if ($galleryProjects->count() > 1)
                                <button type="button" class="service-gallery-button service-gallery-button--prev" id="service-gallery-prev" aria-label="Previous work photo">&#10094;</button>
                            @endif
                            @php
                                $firstGalleryProject = $galleryProjects->first();
                                $firstGalleryImage = '/' . ltrim((string) $firstGalleryProject->image_path, '/');
                            @endphp
                            <img id="service-gallery-main-image" src="{{ $firstGalleryImage }}" alt="{{ $firstGalleryProject->title }}">
                            @if ($galleryProjects->count() > 1)
                                <button type="button" class="service-gallery-button service-gallery-button--next" id="service-gallery-next" aria-label="Next work photo">&#10095;</button>
                            @endif
                        @else
                            <div class="service-gallery-empty">
                                <p>Portfolio photos will appear here once {{ $providerName }} adds previous or ongoing project images.</p>
                            </div>
                        @endif
                    </div>

                    @if ($galleryProjects->count() > 1)
                        <div class="service-gallery-thumbs" aria-label="Work photo thumbnails">
                            @foreach ($galleryProjects as $index => $project)
                                @php($thumbUrl = '/' . ltrim((string) $project->image_path, '/'))
                                <button type="button" class="service-gallery-thumb {{ $index === 0 ? 'is-active' : '' }}" data-service-gallery-thumb data-src="{{ $thumbUrl }}" data-alt="{{ $project->title }}">
                                    <img src="{{ $thumbUrl }}" alt="{{ $project->title }}">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h2 style="margin-top: 0;">Provider Details</h2>
                    <div class="service-detail-grid">
                        <div class="service-detail-item">
                            <span>Main service</span>
                            <strong>{{ $serviceTypeLabel }}</strong>
                        </div>
                        <div class="service-detail-item">
                            <span>Base location</span>
                            <strong>{{ $provider->location ?: 'Nigeria' }}</strong>
                        </div>
                        <div class="service-detail-item">
                            <span>Work photos</span>
                            <strong>{{ number_format((int) ($provider->portfolio_count ?? 0)) }}</strong>
                        </div>
                    </div>

                    <h3 style="margin: 1rem 0 0.45rem; font-size: 1.05rem;">About this provider</h3>
                    <p style="margin: 0; color: var(--neutral-700);">
                        {!! nl2br(e((string) ($provider->business_description ?: 'This provider has not added a detailed profile yet. You can still submit a request and NaijaBuilders will confirm fit before connecting you.'))) !!}
                    </p>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h2 style="margin-top: 0;">Previous and Ongoing Work</h2>
                    @if ($portfolioProjects->count() > 0)
                        <div class="service-project-grid">
                            @foreach ($portfolioProjects as $project)
                                @php
                                    $projectImagePath = trim((string) ($project->image_path ?? ''));
                                    $projectImageUrl = $projectImagePath !== '' ? '/' . ltrim($projectImagePath, '/') : '';
                                    $status = (string) ($project->status ?? 'completed');
                                @endphp
                                <article class="service-project">
                                    <div class="service-project__media">
                                        @if ($projectImageUrl !== '')
                                            <img src="{{ $projectImageUrl }}" alt="{{ $project->title }}">
                                        @else
                                            <span>No photo added</span>
                                        @endif
                                    </div>
                                    <div class="service-project__body">
                                        <span class="service-status service-status--{{ $status === 'ongoing' ? 'ongoing' : 'completed' }}">{{ ucfirst($status) }}</span>
                                        <h3>{{ $project->title }}</h3>
                                        @if (trim((string) ($project->location ?? '')) !== '')
                                            <p>{{ $project->location }}</p>
                                        @endif
                                        @if (trim((string) ($project->description ?? '')) !== '')
                                            <p>{{ \Illuminate\Support\Str::limit((string) $project->description, 130) }}</p>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <div class="service-empty">No previous or ongoing work has been uploaded for this provider yet.</div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h2 style="margin-top: 0;">Reviews</h2>
                    @if ($reviews->count() > 0)
                        <div class="service-review-list">
                            @foreach ($reviews as $review)
                                @php($reviewRounded = (int) $review->rating)
                                <article class="service-review">
                                    <div class="service-review__head">
                                        <h4>{{ trim((string) ($review->reviewer_company ?? '')) !== '' ? $review->reviewer_company : $review->reviewer_full_name }}</h4>
                                        <span class="service-stars" aria-label="Review rating">
                                            @for ($star = 1; $star <= 5; $star++)
                                                <span class="{{ $star <= $reviewRounded ? '' : 'is-empty' }}">&#9733;</span>
                                            @endfor
                                        </span>
                                    </div>
                                    @if (trim((string) ($review->review_text ?? '')) !== '')
                                        <p>{{ $review->review_text }}</p>
                                    @endif
                                    <time datetime="{{ $review->created_at }}">{{ \Illuminate\Support\Carbon::parse($review->created_at)->format('M d, Y') }}</time>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <div class="service-empty">No reviews yet. Ratings from completed buyer experiences will appear here.</div>
                    @endif
                </div>
            </div>
        </section>

        <aside class="service-profile-side" id="request">
            <div class="card">
                <div class="card-body">
                    <h2 style="margin-top: 0;">Request this Provider</h2>
                    <p style="margin-top: 0; color: var(--neutral-600);">Send your project details to NaijaBuilders so the request can be reviewed and routed to {{ $providerName }}.</p>

                    <form method="POST" action="/hire-service.php">
                        @csrf
                        <input type="hidden" name="service_provider_id" value="{{ (int) $provider->id }}">
                        <input type="hidden" name="service_type" value="{{ $providerServiceType }}">

                        <div class="form-group">
                            <label for="provider_project_title">Project title</label>
                            <input id="provider_project_title" name="project_title" type="text" required value="{{ old('project_title') }}" placeholder="Site wiring and fittings">
                            @error('project_title')<p class="text-note">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-group">
                            <label for="provider_project_location">Project location</label>
                            <input id="provider_project_location" name="project_location" type="text" required value="{{ old('project_location', $user?->location ?? '') }}" placeholder="Lekki, Lagos">
                            @error('project_location')<p class="text-note">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-group">
                            <label for="provider_project_description">What do you need done?</label>
                            <textarea id="provider_project_description" name="project_description" required rows="5" placeholder="Describe the project, site stage, and support you need.">{{ old('project_description') }}</textarea>
                            @error('project_description')<p class="text-note">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-group">
                            <label for="provider_budget_range">Budget range</label>
                            <select id="provider_budget_range" name="budget_range">
                                <option value="">Select budget</option>
                                @foreach ($budgetRanges as $value => $label)
                                    <option value="{{ $value }}" {{ old('budget_range') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('budget_range')<p class="text-note">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-group">
                            <label for="provider_preferred_start_date">Preferred start date</label>
                            <input id="provider_preferred_start_date" name="preferred_start_date" type="date" value="{{ old('preferred_start_date') }}">
                            @error('preferred_start_date')<p class="text-note">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-group">
                            <label for="provider_contact_name">Contact name</label>
                            <input id="provider_contact_name" name="contact_name" type="text" required value="{{ $prefillName }}">
                            @error('contact_name')<p class="text-note">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-group">
                            <label for="provider_contact_phone">Contact phone</label>
                            <input id="provider_contact_phone" name="contact_phone" type="tel" required value="{{ $prefillPhone }}" placeholder="+234 801 234 5678">
                            @error('contact_phone')<p class="text-note">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-group">
                            <label for="provider_contact_email">Contact email</label>
                            <input id="provider_contact_email" name="contact_email" type="email" required value="{{ $prefillEmail }}" placeholder="you@example.com">
                            @error('contact_email')<p class="text-note">{{ $message }}</p>@enderror
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">Send Request</button>
                    </form>
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection

@push('scripts')
@if ($galleryProjects->count() > 1)
<script>
(function () {
    const mainImage = document.getElementById('service-gallery-main-image');
    const thumbnails = Array.from(document.querySelectorAll('[data-service-gallery-thumb]'));
    const prevButton = document.getElementById('service-gallery-prev');
    const nextButton = document.getElementById('service-gallery-next');

    if (!mainImage || thumbnails.length === 0) {
        return;
    }

    let currentIndex = 0;
    const setActive = function (nextIndex) {
        const total = thumbnails.length;
        currentIndex = (nextIndex + total) % total;
        const activeThumb = thumbnails[currentIndex];
        mainImage.src = activeThumb.getAttribute('data-src') || mainImage.src;
        mainImage.alt = activeThumb.getAttribute('data-alt') || mainImage.alt;
        thumbnails.forEach(function (thumb, index) {
            thumb.classList.toggle('is-active', index === currentIndex);
        });
    };

    thumbnails.forEach(function (thumb, index) {
        thumb.addEventListener('click', function () {
            setActive(index);
        });
    });

    if (prevButton) {
        prevButton.addEventListener('click', function () {
            setActive(currentIndex - 1);
        });
    }

    if (nextButton) {
        nextButton.addEventListener('click', function () {
            setActive(currentIndex + 1);
        });
    }
})();
</script>
@endif
@endpush
