{{-- resources/views/pages/welcome.blade.php --}}
@extends('layouts.app') {{-- Use the main app layout --}}

@section('title', 'Welcome Home') {{-- Set the specific title for this page --}}

@section('content') {{-- Define the main content section --}}
<div class="container welcome-page"> {{-- Use the .container class from app.blade.php --}}

    {{-- Welcome Section --}}
    <section class="welcome-hero">
        <h1 class="welcome-title">
            Welcome to MakeupAI
        </h1>
        <p class="welcome-subtitle">
            Discover your perfect makeup style with our AI-powered recommendation system. Analyze your features, get personalized advice, and virtually try on new looks.
        </p>
        <a href="{{ route('analyze') }}" class="button button-pink button-arrow">
            Get Started <i class="fa-solid fa-arrow-right-long"></i>
        </a>
    </section>

    {{-- How It Works Section --}}
    <section class="how-it-works">
        <h2 class="section-title">
            How It Works
        </h2>
        <div class="feature-grid">
            {{-- Feature Card 1 --}}
            <div class="feature-card">
                <i class="fa-solid fa-face-smile-beam card-icon"></i>
                <h3 class="card-title">Facial Feature Analysis</h3>
                <p class="card-description">Upload your photo and let our AI identify your unique skin tone, face shape, and eye color.</p>
                <a href="#" class="card-link">Explore →</a>
            </div>
            {{-- Feature Card 2 --}}
            <div class="feature-card">
                 <i class="fa-solid fa-bookmark card-icon"></i>
                <h3 class="card-title">Save Your Favorite Looks</h3>
                <p class="card-description">Keep a personal collection of your favorite makeup recommendations to revisit anytime.</p>
                 <a href="#" class="card-link">Explore →</a>
            </div>
            {{-- Feature Card 3 --}}
            <div class="feature-card">
                 <i class="fa-solid fa-video card-icon"></i>
                <h3 class="card-title">Makeup Tutorials</h3>
                <p class="card-description">Learn new techniques and master your makeup skills with our curated library of expert video guides.</p>
                 <a href="#" class="card-link">Explore →</a>
            </div>
        </div>
    </section>

    {{-- Ready to Transform Section --}}
    <section class="transform-section">
         <h2 class="section-title-light">
            Ready to Transform Your Look?
        </h2>
        <p>
             It's simple, fast, and fun! Click below to start your AI-powered makeup journey.
        </p>
         <a href="{{ route('analyze') }}" class="button button-pink">
            Start Analysis
        </a>
    </section>

</div>
@endsection {{-- End the main content section --}}

