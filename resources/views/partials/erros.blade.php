@if ($errors->any())
<div class="alert alert-danger" role="alert">
    <strong>Corrija os campos abaixo:</strong>
    <ul class="mb-0 mt-1">
        @foreach ($errors->all() as $erro)
            <li>{{ $erro }}</li>
        @endforeach
    </ul>
</div>
@endif
