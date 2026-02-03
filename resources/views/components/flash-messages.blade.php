<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if(session('success'))
            window.toast && window.toast.success(@json(session('success')));
        @endif

        @if(session('error'))
            window.toast && window.toast.error(@json(session('error')));
        @endif

        @if($errors->any())
            @foreach($errors->all() as $error)
                window.toast && window.toast.error(@json($error));
            @endforeach
        @endif
    });
</script>
