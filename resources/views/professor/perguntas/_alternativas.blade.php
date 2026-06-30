@php
    $editando = isset($pergunta);
@endphp

@foreach ($cores as $posicao => $info)
    @php
        $alt = $editando ? $pergunta->alternativas->firstWhere('ordem', $posicao) : null;
        $valor = old("alternativas.$posicao.texto", $alt?->texto ?? '');
        $corretaSalva = $editando ? optional($pergunta->alternativas->firstWhere('correta', true))?->ordem : null;
        $marcada = ((string) old('correta', $corretaSalva)) === ((string) $posicao);
    @endphp
    <div class="alternativa-row alternativa-{{ $info['cor'] }}">
        <span class="alt-shape alt-shape-{{ $info['cor'] }}">{{ $info['forma'] }}</span>
        <input type="text"
               name="alternativas[{{ $posicao }}][texto]"
               value="{{ $valor }}"
               class="form-control @error("alternativas.$posicao.texto") is-invalid @enderror"
               placeholder="Texto da opção {{ $posicao }}"
               required>
        <div class="form-check ms-2">
            <input class="form-check-input" type="radio" name="correta" id="correta_{{ $posicao }}" value="{{ $posicao }}" {{ $marcada ? 'checked' : '' }}>
            <label class="form-check-label" for="correta_{{ $posicao }}">correta</label>
        </div>
    </div>
@endforeach
