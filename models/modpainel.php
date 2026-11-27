<?php

function condicoesPainel($estado, $cidade, $bairro, $codMot, &$params)
{
    $condicoes = array();

    if ($estado !== '') {
        $condicoes[] = 'TB02176_ESTADO = ?';
        $params[] = $estado;
    }
    if ($cidade !== '') {
        $condicoes[] = 'TB02176_CIDADE = ?';
        $params[] = $cidade;
    }
    if ($bairro !== '') {
        $condicoes[] = 'TB02176_BAIRRO = ?';
        $params[] = $bairro;
    }
    if ($codMot !== '') {
        $condicoes[] = 'TB01077_CODIGO = ?';
        $params[] = $codMot;
    }
    return count($condicoes) > 0 ? ' AND ' . implode(' AND ', $condicoes) : '';
}

function buscarRequisicoesPainel($conn, $StatusComMot, $SituacaoMotoristaAtivo, $estado, $cidade, $bairro, $codMot)
{
    $params = array($SituacaoMotoristaAtivo, $StatusComMot);
    $filtros = condicoesPainel($estado, $cidade, $bairro, $codMot, $params);
    $sql = "
        SELECT DISTINCT
            TB01077_CODIGO CodMot,
            CAST(TB01077_NOME AS VARCHAR(MAX)) NomeMot,
            TB02021_CODIGO Req,
            TB02021_NTFISC Nota,
            FORMAT(TB02021_DATA, 'dd/MM/yyyy') DataAbertura,
            TB01008_FANTASIA NomeCli,
            TB02176_ESTADO Estado,
            TB02176_CIDADE Cidade,
            TB02176_BAIRRO Bairro,
            TB02021_STATUS CodStatus,
            TB01021_NOME NomeStatus
        FROM TB01077
        INNER JOIN TB02096 ON TB02096_CODMOTO = TB01077_CODIGO
        INNER JOIN TB02021 ON TB02021_CODIGO = TB02096_CODIGO
        LEFT JOIN TB01008 ON TB01008_CODIGO = TB02021_CODCLI
        LEFT JOIN TB02176 ON TB02176_CODIGO = TB02021_CODSITE
        LEFT JOIN TB01021 ON TB01021_CODIGO = TB02021_STATUS
        WHERE TB01077_SITUACAO = ?
          AND TB02021_STATUS = ?
          $filtros
        ORDER BY NomeMot, Req
    ";

    $stmt = sqlsrv_query($conn, $sql, $params);
    $rows = array();
    if ($stmt === false) return $rows;

    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $rows[] = $row;
    }
    return $rows;
}

function buscarOpcoesPainel($conn, $StatusComMot, $SituacaoMotoristaAtivo, $estadoSelecionado, $cidadeSelecionada, $bairroSelecionado)
{
    $sql = "
        SELECT DISTINCT
            TB01077_CODIGO CodMot,
            TB01077_NOME NomeMot,
            TB02176_ESTADO Estado,
            TB02176_CIDADE Cidade,
            TB02176_BAIRRO Bairro
        FROM TB01077
        INNER JOIN TB02096 ON TB02096_CODMOTO = TB01077_CODIGO
        INNER JOIN TB02021 ON TB02021_CODIGO = TB02096_CODIGO
        LEFT JOIN TB02176 ON TB02176_CODIGO = TB02021_CODSITE
        WHERE TB01077_SITUACAO = ?
          AND TB02021_STATUS = ?
        ORDER BY TB01077_NOME
    ";
    $stmt = sqlsrv_query($conn, $sql, array($SituacaoMotoristaAtivo, $StatusComMot));
    $opcoes = array('motoristas' => array(), 'estados' => array(), 'cidades' => array(), 'bairros' => array());
    if ($stmt === false) return $opcoes;

    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $codigo = trim((string) $row['CodMot']);
        $nome = trim((string) $row['NomeMot']);
        $estado = trim((string) $row['Estado']);
        $cidade = trim((string) $row['Cidade']);
        $bairro = trim((string) $row['Bairro']);

        if ($estado !== '') $opcoes['estados'][$estado] = $estado;

        $correspondeEstado = $estadoSelecionado === '' || $estado === $estadoSelecionado;
        $correspondeCidade = $cidadeSelecionada === '' || $cidade === $cidadeSelecionada;
        $correspondeBairro = $bairroSelecionado === '' || $bairro === $bairroSelecionado;

        if ($correspondeEstado && $cidade !== '') {
            $opcoes['cidades'][$cidade] = $cidade;
        }
        if ($correspondeEstado && $correspondeCidade && $bairro !== '') {
            $opcoes['bairros'][$bairro] = $bairro;
        }
        if ($correspondeEstado && $correspondeCidade && $correspondeBairro && $codigo !== '') {
            $opcoes['motoristas'][$codigo] = $nome;
        }
    }

    asort($opcoes['motoristas'], SORT_NATURAL | SORT_FLAG_CASE);
    ksort($opcoes['estados'], SORT_NATURAL | SORT_FLAG_CASE);
    ksort($opcoes['cidades'], SORT_NATURAL | SORT_FLAG_CASE);
    ksort($opcoes['bairros'], SORT_NATURAL | SORT_FLAG_CASE);
    return $opcoes;
}

function agruparRequisicoesPainel($rows)
{
    $motoristas = array();

    foreach ($rows as $row) {
        $codigo = trim((string) $row['CodMot']);
        if (!isset($motoristas[$codigo])) {
            $motoristas[$codigo] = array(
                'codigo' => $codigo,
                'nome' => $row['NomeMot'],
                'total' => 0,
                'requisicoes' => array()
            );
        }

        $motoristas[$codigo]['total']++;
        $motoristas[$codigo]['requisicoes'][] = $row;
    }

    return array_values($motoristas);
}
