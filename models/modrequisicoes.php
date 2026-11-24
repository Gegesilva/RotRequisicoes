<?php

function normalizarRequisicoesSelecionadas($requisicoes)
{
    $selecionadas = array();

    foreach ($requisicoes as $requisicao) {
        $codigo = trim((string) $requisicao);

        if ($codigo == '' || isset($selecionadas[$codigo])) {
            continue;
        }

        $selecionadas[$codigo] = $codigo;
    }

    return array_values($selecionadas);
}

function enviarRequisicoesSelecionadas($conn, $requisicoes, $codMot, $login, $nomeMot, $StatusComMot)
{
    $selecionadas = normalizarRequisicoesSelecionadas($requisicoes);

    foreach ($selecionadas as $requisicao) {
        enviaReqTec($conn, $requisicao, $codMot, $login, $nomeMot, $StatusComMot);
    }
}
