@extends('layouts.app')

@section('title', 'Iniciar Sesión')
@section('page-id', 'auth.login')

@section('content')
<style>
/* ===== SPLASH SCREEN ===== */
.splash-screen {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: var(--md-sys-color-surface);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    opacity: 1;
    transition: opacity 0.5s ease-out;
}

.splash-screen.fade-out {
    opacity: 0;
    pointer-events: none;
}

.splash-clock {
    width: 120px;
    height: 120px;
    opacity: 0;
    transform: scale(0.5);
    animation: splashClockIn 0.6s ease-out 0.2s forwards;
}

@keyframes splashClockIn {
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.splash-clock .clock-face {
    fill: none;
    stroke: var(--md-sys-color-primary);
    stroke-width: 1;
}

.splash-clock .clock-center {
    fill: var(--md-sys-color-primary);
}

.splash-clock .clock-hand {
    stroke: var(--md-sys-color-primary);
    stroke-linecap: round;
    transform-origin: 12px 12px;
}

.splash-clock .hour-hand {
    stroke-width: 0.8;
    animation: splashHourHand 1.5s ease-out 0.5s forwards;
}

.splash-clock .minute-hand {
    stroke-width: 0.6;
    animation: splashMinuteHand 1.2s ease-out 0.5s forwards;
}

@keyframes splashHourHand {
    0% { transform: rotate(-60deg); }
    100% { transform: rotate(120deg); }
}

@keyframes splashMinuteHand {
    0% { transform: rotate(-120deg); }
    100% { transform: rotate(360deg); }
}

.splash-title {
    margin-top: 24px;
    font-size: 2rem;
    font-weight: 600;
    color: var(--md-sys-color-on-surface);
    opacity: 0;
    transform: translateY(16px);
    animation: splashTitleIn 0.5s ease-out 0.6s forwards;
}

@keyframes splashTitleIn {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* ===== LOGIN PAGE ===== */
.login-container {
    opacity: 0;
}

.login-container.visible {
    animation: fadeSlideUp 0.4s ease-out forwards;
}

@keyframes fadeSlideUp {
    from {
        opacity: 0;
        transform: translateY(24px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes scaleIn {
    from {
        opacity: 0;
        transform: scale(0.8);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

@keyframes rotateHourHand {
    0% { transform: rotate(-30deg); }
    100% { transform: rotate(60deg); }
}

@keyframes rotateMinuteHand {
    0% { transform: rotate(-90deg); }
    100% { transform: rotate(180deg); }
}

.animated-clock {
    width: 64px;
    height: 64px;
}

.clock-face {
    fill: none;
    stroke: var(--md-sys-color-primary);
    stroke-width: 1.2;
}

.clock-center {
    fill: var(--md-sys-color-primary);
}

.clock-hand {
    stroke: var(--md-sys-color-primary);
    stroke-linecap: round;
    transform-origin: 12px 12px;
}

.login-container .hour-hand {
    stroke-width: 1;
    transform: rotate(60deg);
}

.login-container .minute-hand {
    stroke-width: 0.8;
    transform: rotate(180deg);
}

/* Interactive mode - after initial animation */
.clock-hand.interactive {
    animation: none !important;
    transition: transform 0.15s ease-out;
}

.login-icon {
    opacity: 0;
}

.login-container.visible .login-icon {
    animation: scaleIn 0.3s ease-out 0.1s forwards;
}

.login-title {
    opacity: 0;
}

.login-container.visible .login-title {
    animation: fadeSlideUp 0.3s ease-out 0.2s forwards;
}

.login-subtitle {
    opacity: 0;
}

.login-container.visible .login-subtitle {
    animation: fadeSlideUp 0.3s ease-out 0.3s forwards;
}

.login-divider {
    opacity: 0;
}

.login-container.visible .login-divider {
    animation: fadeSlideUp 0.3s ease-out 0.4s forwards;
}

.login-field-1 {
    opacity: 0;
}

.login-container.visible .login-field-1 {
    animation: fadeSlideUp 0.3s ease-out 0.5s forwards;
}

.login-field-2 {
    opacity: 0;
}

.login-container.visible .login-field-2 {
    animation: fadeSlideUp 0.3s ease-out 0.6s forwards;
}

.login-button {
    opacity: 0;
}

.login-container.visible .login-button {
    animation: fadeSlideUp 0.3s ease-out 0.7s forwards;
}
</style>

<!-- Splash Screen -->
<div class="splash-screen" id="splash-screen">
    <svg class="splash-clock" viewBox="0 0 24 24">
        <circle class="clock-face" cx="12" cy="12" r="10"/>
        <line x1="12" y1="3" x2="12" y2="4.5" stroke="var(--md-sys-color-primary)" stroke-width="0.8"/>
        <line x1="12" y1="19.5" x2="12" y2="21" stroke="var(--md-sys-color-primary)" stroke-width="0.8"/>
        <line x1="3" y1="12" x2="4.5" y2="12" stroke="var(--md-sys-color-primary)" stroke-width="0.8"/>
        <line x1="19.5" y1="12" x2="21" y2="12" stroke="var(--md-sys-color-primary)" stroke-width="0.8"/>
        <line class="clock-hand hour-hand" x1="12" y1="12" x2="12" y2="7"/>
        <line class="clock-hand minute-hand" x1="12" y1="12" x2="12" y2="5"/>
        <circle class="clock-center" cx="12" cy="12" r="0.6"/>
    </svg>
    <div class="splash-title">TimeTrack</div>
</div>

<!-- Login Content -->
<div class="row login-container" id="login-container">
    <div class="col s12 l6 offset-l3">
        <div class="card">
            <div class="card-content">
                <div class="center-align login-icon" style="margin-bottom: 16px;">
                    <svg class="animated-clock" viewBox="0 0 24 24">
                        <!-- Clock face -->
                        <circle class="clock-face" cx="12" cy="12" r="10"/>
                        <!-- Hour markers -->
                        <line x1="12" y1="3" x2="12" y2="5" stroke="var(--md-sys-color-primary)" stroke-width="1"/>
                        <line x1="12" y1="19" x2="12" y2="21" stroke="var(--md-sys-color-primary)" stroke-width="1"/>
                        <line x1="3" y1="12" x2="5" y2="12" stroke="var(--md-sys-color-primary)" stroke-width="1"/>
                        <line x1="19" y1="12" x2="21" y2="12" stroke="var(--md-sys-color-primary)" stroke-width="1"/>
                        <!-- Hour hand (short) -->
                        <line id="hour-hand" class="clock-hand hour-hand" x1="12" y1="12" x2="12" y2="7"/>
                        <!-- Minute hand (long) -->
                        <line id="minute-hand" class="clock-hand minute-hand" x1="12" y1="12" x2="12" y2="5"/>
                        <!-- Center dot -->
                        <circle class="clock-center" cx="12" cy="12" r="0.8"/>
                    </svg>
                </div>
                <span class="card-title center-align login-title">Iniciar Sesión</span>
                <p class="text-secondary center-align login-subtitle">
                    O <a href="{{ route('register') }}" class="text-claude">crear una cuenta nueva</a>
                </p>

                <div class="divider login-divider" style="margin: 20px 0;"></div>

                <form action="{{ route('login') }}" method="POST" id="login-form">
                    @csrf
                    {{-- Hidden inputs for form submission (Material Web compatibility) --}}
                    <input type="hidden" name="email" id="email-hidden" value="{{ old('email') }}">
                    <input type="hidden" name="password" id="password-hidden" value="">

                    <div class="row login-field-1">
                        <div class="col s12" style="margin-bottom: 16px;">
                            <md-outlined-text-field
                                id="email"
                                type="email"
                                label="Email"
                                autocomplete="email"
                                required
                                value="{{ old('email') }}"
                                style="width: 100%;"
                                @error('email') error error-text="{{ $message }}" @enderror
                            >
                                <md-icon slot="leading-icon">email</md-icon>
                            </md-outlined-text-field>
                        </div>
                    </div>
                    <div class="row login-field-2">
                        <div class="col s12" style="margin-bottom: 16px;">
                            <md-outlined-text-field
                                id="password"
                                type="password"
                                label="Contraseña"
                                autocomplete="current-password"
                                required
                                style="width: 100%;"
                                @error('password') error error-text="{{ $message }}" @enderror
                            >
                                <md-icon slot="leading-icon">lock</md-icon>
                            </md-outlined-text-field>
                        </div>
                    </div>
                    <div class="row login-button">
                        <div class="col s12">
                            <md-filled-button type="submit" style="width: 100%;">
                                <md-icon slot="icon">login</md-icon>
                                Iniciar Sesión
                            </md-filled-button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

