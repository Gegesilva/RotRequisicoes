(function () {
    function limpar(formulario, nomes) {
        for (var i = 0; i < nomes.length; i++) {
            var campo = formulario.elements[nomes[i]];
            if (campo) campo.value = '';
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        var formulario = document.getElementById('filtrosPainel');
        if (!formulario) return;

        formulario.addEventListener('change', function (event) {
            var nome = event.target.name;

            if (nome === 'estado') {
                limpar(formulario, ['cidade', 'bairro', 'codMot']);
            } else if (nome === 'cidade') {
                limpar(formulario, ['bairro', 'codMot']);
            } else if (nome === 'bairro') {
                limpar(formulario, ['codMot']);
            }

            if (nome === 'estado' || nome === 'cidade' || nome === 'bairro' || nome === 'codMot') {
                if (window.mostrarCarregamento) window.mostrarCarregamento();
                formulario.submit();
            }
        });
    });
})();
