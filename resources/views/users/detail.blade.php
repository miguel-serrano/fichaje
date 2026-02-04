@extends('layouts.app')

@section('title', 'Mi Información')
@section('page-id', 'user.detail')

@section('content')
<style>
    /* Responsive para collapsible headers en móvil */
    @media only screen and (max-width: 600px) {
        .collapsible-header {
            flex-wrap: wrap;
            padding: 10px 15px;
        }
        .collapsible-header > span:first-of-type {
            width: 100%;
            margin-bottom: 8px;
        }
        .collapsible-header .chip {
            margin: 2px 4px 2px 0;
            font-size: 0.85rem;
        }
    }
    /* Personal info collapsible */
    .personal-info-details summary::-webkit-details-marker { display: none; }
    .personal-info-details summary { list-style: none; }
    .personal-info-details[open] .collapse-icon { transform: rotate(180deg); }
</style>

@include('users.partials.personal-info', ['user' => $user])

<!-- Aviso de cuenta inactiva -->
@if(!$user->isActive())
<div class="row">
    <div class="col s12">
        <div class="card" style="background: var(--warning-bg) !important;">
            <div class="card-content" style="color: var(--warning) !important;">
                <div class="row valign-wrapper" style="margin-bottom: 0;">
                    <div class="col s12 m1 center-align">
                        <md-icon style="font-size: 48px; width: 48px; height: 48px; color: var(--warning);">warning</md-icon>
                    </div>
                    <div class="col s12 m11">
                        <h5 style="margin-top: 0; color: var(--warning);">Cuenta Inactiva</h5>
                        <p>
                            Tu cuenta está pendiente de activación. No podrás fichar entrada ni salida hasta que un administrador active tu cuenta.
                            Por favor, contacta con un administrador.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@php
    $tieneAbiertoHoy = collect($allRegistros)->contains(fn($r) => $r->isOpen());
@endphp

@include('users.partials.today-entries', ['allRegistros' => $allRegistros])
@include('users.partials.daily-summary', ['dailyRegistros' => $dailyRegistros])
@include('users.partials.hours-balance', ['totalMes' => $totalMes, 'dailyRegistros' => $dailyRegistros])
@include('users.partials.monthly-summary', ['monthlyRegistros' => $monthlyRegistros, 'totalMes' => $totalMes, 'tieneAbiertoHoy' => $tieneAbiertoHoy])
@endsection

@push('page-data')
<script>window.__pageData = {};</script>
@endpush
