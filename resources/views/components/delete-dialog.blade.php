@props([
    'dialogId',
    'formId',
    'title' => 'Confirmar eliminacion',
    'cancelId' => null,
])

<md-dialog id="{{ $dialogId }}">
    <div slot="headline">
        <md-icon style="color: var(--error); vertical-align: middle; margin-right: 8px;">warning</md-icon>
        {{ $title }}
    </div>
    <form slot="content" id="{{ $formId }}" method="POST">
        @csrf
        @method('DELETE')
        {{ $slot }}
    </form>
    <div slot="actions">
        <md-text-button form="{{ $formId }}" value="cancel" type="button" @if($cancelId) id="{{ $cancelId }}" @endif>Cancelar</md-text-button>
        <md-filled-button form="{{ $formId }}" type="submit" style="--md-filled-button-container-color: var(--error);">
            <md-icon slot="icon">delete</md-icon>
            Eliminar
        </md-filled-button>
    </div>
</md-dialog>
