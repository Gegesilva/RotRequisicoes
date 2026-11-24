<?php
include_once __DIR__ . "/../config/config.php";
function filtroMot($conn, $nomeMot, $SituacaoMotoristaAtivo)
{
    $sql = "SELECT 
                TB01077_CODIGO CodMot,
                TB01077_NOME NomeMot
            FROM TB01077
            WHERE 
            TB01077_SITUACAO = '$SituacaoMotoristaAtivo'
            ORDER BY TB01077_NOME
        ";

    $stmt = sqlsrv_query($conn, $sql);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }


    $opcao1 = "";

    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $opcao1 .= "<option name='Motorista' value='$row[CodMot]'>$row[NomeMot]</option>";

    }

    $textoMotorista = trim((string) $nomeMot) != '' ? $nomeMot : 'Selecione o motorista';
    $classeMotorista = trim((string) $nomeMot) == '' ? " class='option-placeholder'" : '';

    $opcao1 .= " <option disabled selected$classeMotorista>$textoMotorista</option>
									             </select>";
    print ($opcao1);
}
function filtroEstado($conn, $StatusAguardMot, $estadoInput)
{
    $sql = "SELECT DISTINCT
                TB02176_ESTADO Estado
            FROM 
                TB02021
            LEFT JOIN TB02176 ON TB02176_CODIGO = TB02021_CODSITE
            WHERE
                TB02021_STATUS IN ($StatusAguardMot) AND
                TB02176_ESTADO IS NOT NULL
                AND TB02176_ESTADO <> ''
            ORDER BY TB02176_ESTADO
        ";

    $stmt = sqlsrv_query($conn, $sql);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }


    $opcao1 = "";

    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $opcao1 .= "<option name='Estado' value='$row[Estado]'>$row[Estado]</option>";

    }

    $textoEstado = trim((string) $estadoInput) != '' ? $estadoInput : 'Filtrar por estado';
    $classeEstado = trim((string) $estadoInput) == '' ? " class='option-placeholder'" : '';

    $opcao1 .= "<option disabled selected$classeEstado>$textoEstado</option>
												</select>";

    print ($opcao1);
}

function filtroCidade($conn, $StatusAguardMot, $estado2, $cidadeInput)
{
    $sql = "SELECT DISTINCT
                TB02176_CIDADE Cidade
            FROM 
                TB02021
            LEFT JOIN TB02176 ON TB02176_CODIGO = TB02021_CODSITE
            WHERE
                TB02021_STATUS IN ($StatusAguardMot) AND
                TB02176_CIDADE IS NOT NULL
                AND TB02176_CIDADE <> ''
                $estado2
            ORDER BY TB02176_CIDADE
        ";

    $stmt = sqlsrv_query($conn, $sql);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }


    $opcao1 = "";

    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $opcao1 .= "<option name='Cidade' value='$row[Cidade]'>$row[Cidade]</option>";

    }

    $textoCidade = trim((string) $cidadeInput) != '' ? $cidadeInput : 'Filtrar por cidade';
    $classeCidade = trim((string) $cidadeInput) == '' ? " class='option-placeholder'" : '';

    $opcao1 .= "<option disabled selected$classeCidade>$textoCidade</option>
												</select>";

    print ($opcao1);
}

function filtroBairro($conn, $StatusAguardMot, $cidade2, $estado2, $bairroInput)
{
    $sql = "SELECT DISTINCT
                TB02176_BAIRRO Bairro
            FROM 
                TB02021
            LEFT JOIN TB02176 ON TB02176_CODIGO = TB02021_CODSITE
            WHERE
                TB02021_STATUS IN ($StatusAguardMot) AND
                TB02176_BAIRRO IS NOT NULL
                AND TB02176_BAIRRO <> ''
                $cidade2
                $estado2
            ORDER BY TB02176_BAIRRO
										";

    $stmt = sqlsrv_query($conn, $sql);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }


    $opcao1 = "";

    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $opcao1 .= "<option name='Bairro' value='$row[Bairro]'>$row[Bairro]</option>";
    }

    $textoBairro = trim((string) $bairroInput) != '' ? $bairroInput : 'Filtrar por bairro';
    $classeBairro = trim((string) $bairroInput) == '' ? " class='option-placeholder'" : '';

    $opcao1 .= "<option disabled selected$classeBairro>$textoBairro</option>
												</select>";

    print ($opcao1);
}
