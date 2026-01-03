@extends('layouts.app')

@section('title', 'Create User')

@section('content')
<div class="row">
    <div class="col s12 m8 offset-m2 l6 offset-l3">
        <div class="card">
            <div class="card-content">
                <span class="card-title">Create New User</span>
                <p class="grey-text">Add a new user to the system.</p>

                <form action="{{ route('user.store') }}" method="POST" style="margin-top: 30px;">
                    @csrf

                    <div class="row">
                        <div class="input-field col s12">
                            <i class="material-icons prefix">person</i>
                            <input
                                type="text"
                                name="name"
                                id="name"
                                value="{{ old('name') }}"
                                required
                                class="validate @error('name') invalid @enderror"
                            >
                            <label for="name">Name</label>
                            @error('name')
                                <span class="helper-text red-text">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="input-field col s12">
                            <i class="material-icons prefix">email</i>
                            <input
                                type="email"
                                name="email"
                                id="email"
                                value="{{ old('email') }}"
                                required
                                class="validate @error('email') invalid @enderror"
                            >
                            <label for="email">Email</label>
                            @error('email')
                                <span class="helper-text red-text">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col s12 right-align">
                            <a href="{{ route('user.index') }}" class="btn-flat waves-effect">Cancel</a>
                            <button type="submit" class="btn waves-effect waves-light light-green">
                                <i class="material-icons right">send</i>Create User
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

