@php
    $pageTitle = 'Hire a Service';
    $prefillName = old('contact_name', $user?->full_name ?? '');
    $prefillEmail = old('contact_email', $user?->email ?? '');
    $prefillPhone = old('contact_phone', $user?->phone ?? '');
@endphp
@extends('layouts.app')

@section('meta_description', 'Hire trusted construction professionals such as architects, site engineers, surveyors, plumbers, electricians, tilers, and project managers on NaijaBuilders.')

@section('content')
<section class="value-section">
    <div class="container">
        <div class="value-header">
            <p class="value-eyebrow">Construction professionals</p>
            <h1>Hire a Service</h1>
            <p class="value-lead">
                Tell us what your project needs. NaijaBuilders will use your request to connect you with relevant construction professionals such as architects, site engineers, surveyors, and skilled trades.
            </p>
        </div>

        @if ($successCode === 'requested')
            <div class="alert alert-success">
                Your service request has been received. A NaijaBuilders team member will follow up with the next step.
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                Please check the form and complete the highlighted details.
            </div>
        @endif

        <div class="grid-2 gap-xl">
            <div class="card">
                <div class="card-body">
                    <h2 class="title-reset">How it works</h2>
                    <ul class="feature-list">
                        <li>Choose the service your project needs.</li>
                        <li>Share the site location, budget range, and a short project brief.</li>
                        <li>NaijaBuilders reviews the request and helps match you with a suitable professional.</li>
                        <li>You can continue ordering materials while the service request is being handled.</li>
                    </ul>
                    <div class="signup-supplier-note" style="margin-top: 1rem;">
                        <h4 style="margin: 0 0 0.5rem;">Want to offer services?</h4>
                        <p style="margin: 0 0 0.8rem;">Create an account as a construction service provider and use the supplier workspace to manage enquiries.</p>
                        <a href="/signup.php?type=service_provider" class="btn btn-outline">Offer Services</a>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h2 class="title-reset">Service request</h2>
                    <form method="POST" action="/hire-service.php">
                        @csrf

                        <div class="form-group">
                            <label for="service_type">Service needed</label>
                            <select id="service_type" name="service_type" required>
                                <option value="">Select service</option>
                                @foreach ($serviceTypes as $value => $label)
                                    <option value="{{ $value }}" {{ old('service_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('service_type')<p class="text-note">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-group">
                            <label for="project_title">Project title</label>
                            <input id="project_title" name="project_title" type="text" required value="{{ old('project_title') }}" placeholder="Duplex design and site supervision">
                            @error('project_title')<p class="text-note">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-group">
                            <label for="project_location">Project location</label>
                            <input id="project_location" name="project_location" type="text" required value="{{ old('project_location', $user?->location ?? '') }}" placeholder="Lekki, Lagos">
                            @error('project_location')<p class="text-note">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-group">
                            <label for="project_description">What do you need done?</label>
                            <textarea id="project_description" name="project_description" required rows="5" placeholder="Describe the project, site stage, and what support you need.">{{ old('project_description') }}</textarea>
                            @error('project_description')<p class="text-note">{{ $message }}</p>@enderror
                        </div>

                        <div class="grid-2 gap-md">
                            <div class="form-group">
                                <label for="budget_range">Budget range</label>
                                <select id="budget_range" name="budget_range">
                                    <option value="">Select budget</option>
                                    @foreach ($budgetRanges as $value => $label)
                                        <option value="{{ $value }}" {{ old('budget_range') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('budget_range')<p class="text-note">{{ $message }}</p>@enderror
                            </div>

                            <div class="form-group">
                                <label for="preferred_start_date">Preferred start date</label>
                                <input id="preferred_start_date" name="preferred_start_date" type="date" value="{{ old('preferred_start_date') }}">
                                @error('preferred_start_date')<p class="text-note">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="contact_name">Contact name</label>
                            <input id="contact_name" name="contact_name" type="text" required value="{{ $prefillName }}">
                            @error('contact_name')<p class="text-note">{{ $message }}</p>@enderror
                        </div>

                        <div class="grid-2 gap-md">
                            <div class="form-group">
                                <label for="contact_phone">Contact phone</label>
                                <input id="contact_phone" name="contact_phone" type="tel" required value="{{ $prefillPhone }}" placeholder="+234 801 234 5678">
                                @error('contact_phone')<p class="text-note">{{ $message }}</p>@enderror
                            </div>

                            <div class="form-group">
                                <label for="contact_email">Contact email</label>
                                <input id="contact_email" name="contact_email" type="email" required value="{{ $prefillEmail }}" placeholder="you@example.com">
                                @error('contact_email')<p class="text-note">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block btn-lg">Send Request</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
