@if(session('success'))
    <div class="row">
        <div class="col s12">
            <div class="card-panel card-panel-success">
                {{ session('success') }}
            </div>
        </div>
    </div>
@endif

@if(session('error'))
    <div class="row">
        <div class="col s12">
            <div class="card-panel card-panel-error">
                {{ session('error') }}
            </div>
        </div>
    </div>
@endif

@if ($errors->any())
    <div class="row">
        <div class="col s12">
            <div class="card-panel card-panel-error">
                <ul style="margin: 0;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif
