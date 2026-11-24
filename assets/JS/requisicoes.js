(function () {
    var atualizacaoEmAndamento = false;

    function definirCarregamento(ativo) {
        var resultados = document.getElementById('resultados');
        atualizacaoEmAndamento = ativo;

        if (resultados) {
            resultados.classList.toggle('resultados-carregando', ativo);
            resultados.setAttribute('aria-busy', ativo ? 'true' : 'false');
        }
    }

    function limparFiltro(formulario, nomeSelect, nomeInput) {
        var select = formulario.querySelector('[name="' + nomeSelect + '"]');
        var input = formulario.querySelector('[name="' + nomeInput + '"]');

        if (select) select.value = '1';
        if (input) input.value = '';
    }

    function prepararFiltrosDependentes(formulario, filtroAlterado) {
        if (!filtroAlterado) return;

        if (filtroAlterado.name === 'Estado') {
            limparFiltro(formulario, 'Cidade', 'cidadeInput');
            limparFiltro(formulario, 'Bairro', 'bairroInput');
        } else if (filtroAlterado.name === 'Cidade') {
            limparFiltro(formulario, 'Bairro', 'bairroInput');
        }
    }

    function atualizarResultados(formulario, filtroAlterado) {
        if (!formulario || atualizacaoEmAndamento) return;

        var deveAtualizarFiltros = formulario.id === 'form-filters';
        prepararFiltrosDependentes(formulario, filtroAlterado);

        var url = formulario.getAttribute('action') || window.location.href;
        var metodo = (formulario.getAttribute('method') || 'GET').toUpperCase();
        var dados = new FormData(formulario);
        var opcoes = {
            method: metodo,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        };

        if (metodo === 'GET') {
            var parametros = new URLSearchParams(dados);
            url += (url.indexOf('?') === -1 ? '?' : '&') + parametros.toString();
        } else {
            opcoes.body = dados;
        }

        definirCarregamento(true);

        fetch(url, opcoes)
            .then(function (resposta) {
                if (!resposta.ok) throw new Error('Falha ao atualizar as requisições.');
                return resposta.text();
            })
            .then(function (html) {
                var documento = new DOMParser().parseFromString(html, 'text/html');
                var novosResultados = documento.getElementById('resultados');
                var resultadosAtuais = document.getElementById('resultados');

                if (!novosResultados || !resultadosAtuais) {
                    throw new Error('A resposta recebida não contém a lista de requisições.');
                }

                resultadosAtuais.replaceWith(novosResultados);

                if (deveAtualizarFiltros) {
                    var novosFiltros = documento.getElementById('form-filters');
                    var filtrosAtuais = document.getElementById('form-filters');

                    if (novosFiltros && filtrosAtuais) {
                        filtrosAtuais.replaceWith(novosFiltros);
                    }
                }

                iniciarSelecaoRequisicoes();
                iniciarArrasteRequisicoes();
            })
            .catch(function (erro) {
                console.error(erro);
                window.alert('Não foi possível atualizar apenas as requisições. A página será recarregada.');
                formulario.submit();
            })
            .finally(function () {
                definirCarregamento(false);
            });
    }

    window.atualizarRequisicoesAjax = atualizarResultados;

    document.addEventListener('submit', function (event) {
        var formulario = event.target;

        if (!(formulario instanceof HTMLFormElement)) return;
        if (event.defaultPrevented) return;
        if (formulario.id === 'form-filters') {
            event.preventDefault();
            atualizarResultados(formulario);
            return;
        }

        if (formulario.closest('#resultados') && formulario.getAttribute('action') === 'index.php') {
            event.preventDefault();
            atualizarResultados(formulario);
        }
    });

    function atualizarSelecaoRequisicoes() {
        var checks = document.querySelectorAll('.check-requisicao');
        var selecionadas = document.querySelectorAll('.check-requisicao:checked');
        var checkTodas = document.getElementById('checkTodasRequisicoes');
        var total = document.getElementById('totalRequisicoesSelecionadas');
        var botao = document.getElementById('btnEnviarSelecionadas');

        if (total) {
            total.textContent = selecionadas.length;
        }

        if (botao) {
            botao.disabled = selecionadas.length === 0;
        }

        if (checkTodas) {
            checkTodas.checked = checks.length > 0 && selecionadas.length === checks.length;
            checkTodas.indeterminate = selecionadas.length > 0 && selecionadas.length < checks.length;
        }
    }

    function iniciarSelecaoRequisicoes() {
        var checkTodas = document.getElementById('checkTodasRequisicoes');
        var checks = document.querySelectorAll('.check-requisicao');
        var formulario = document.getElementById('formRequisicoesSelecionadas');

        if (checkTodas) {
            checkTodas.addEventListener('change', function () {
                for (var i = 0; i < checks.length; i++) {
                    checks[i].checked = checkTodas.checked;
                }

                atualizarSelecaoRequisicoes();
            });
        }

        for (var i = 0; i < checks.length; i++) {
            checks[i].addEventListener('change', atualizarSelecaoRequisicoes);
        }

        if (formulario) {
            formulario.addEventListener('submit', function (event) {
                var selecionadas = document.querySelectorAll('.check-requisicao:checked');
                var tecnico = formulario.querySelector('input[name="codMot"]');

                if (selecionadas.length === 0) {
                    event.preventDefault();
                    window.alert('Selecione ao menos uma requisição.');
                    return;
                }

                if (!tecnico || tecnico.value === '') {
                    event.preventDefault();
                    window.alert('Selecione um técnico antes de enviar as requisições.');
                }
            });
        }

        atualizarSelecaoRequisicoes();
    }

    function iniciarArrasteRequisicoes() {
        var itens = document.querySelectorAll('.req-arrastavel');
        var containers = document.querySelectorAll('.container-arraste');
        var dadosArraste = null;

        function limparDestaques() {
            for (var i = 0; i < containers.length; i++) {
                containers[i].classList.remove('arraste-disponivel', 'arraste-sobre');
            }
        }

        function destinoValido(container) {
            if (!dadosArraste) return false;

            var destino = container.getAttribute('data-destino-arraste');
            return destino && destino !== dadosArraste.origem;
        }

        function enviarParaMotorista(requisicao) {
            var formulario = document.getElementById('formDropMotorista');
            if (!formulario) return;

            var motorista = formulario.querySelector('input[name="codMot"]');
            if (!motorista || motorista.value === '') {
                window.alert('Selecione um motorista antes de enviar a requisição.');
                return;
            }

            formulario.querySelector('input[name="req"]').value = requisicao;
            atualizarResultados(formulario);
        }

        function devolverParaRoteirizacao(requisicao) {
            var formulario = document.getElementById('formDropRoteirizacao');
            if (!formulario) return;

            formulario.querySelector('input[name="reqLinhaRet"]').value = requisicao;
            atualizarResultados(formulario);
        }

        for (var i = 0; i < itens.length; i++) {
            itens[i].addEventListener('dragstart', function (event) {
                dadosArraste = {
                    requisicao: this.getAttribute('data-req') || '',
                    origem: this.getAttribute('data-origem-arraste') || ''
                };

                this.classList.add('requisicao-arrastando');
                event.dataTransfer.effectAllowed = 'move';
                event.dataTransfer.setData('text/plain', dadosArraste.requisicao);

                for (var c = 0; c < containers.length; c++) {
                    if (destinoValido(containers[c])) {
                        containers[c].classList.add('arraste-disponivel');
                    }
                }
            });

            itens[i].addEventListener('dragend', function () {
                this.classList.remove('requisicao-arrastando');
                limparDestaques();
                dadosArraste = null;
            });
        }

        for (var j = 0; j < containers.length; j++) {
            containers[j].addEventListener('dragover', function (event) {
                if (!destinoValido(this)) return;

                event.preventDefault();
                event.dataTransfer.dropEffect = 'move';
                this.classList.add('arraste-sobre');
            });

            containers[j].addEventListener('dragleave', function (event) {
                if (!this.contains(event.relatedTarget)) {
                    this.classList.remove('arraste-sobre');
                }
            });

            containers[j].addEventListener('drop', function (event) {
                if (!destinoValido(this)) return;

                event.preventDefault();
                var requisicao = dadosArraste.requisicao || event.dataTransfer.getData('text/plain');
                var destino = this.getAttribute('data-destino-arraste');

                limparDestaques();

                if (!requisicao) return;

                if (destino === 'motorista') {
                    enviarParaMotorista(requisicao);
                } else if (destino === 'roteirizacao') {
                    devolverParaRoteirizacao(requisicao);
                }
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            iniciarSelecaoRequisicoes();
            iniciarArrasteRequisicoes();
        });
    } else {
        iniciarSelecaoRequisicoes();
        iniciarArrasteRequisicoes();
    }
}());
