<?php
header('Content-type: text/html; charset=utf-8');
session_start();

include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../config/config.php';
include_once __DIR__ . '/../models/modpainel.php';

$login = isset($_SESSION['login']) ? $_SESSION['login'] : '';
$senha = isset($_SESSION['password']) ? $_SESSION['password'] : '';
$stmtUsuario = sqlsrv_query(
    $conn,
    'SELECT TOP 1 TB01066_USUARIO Usuario FROM TB01066 WHERE TB01066_USUARIO = ? AND TB01066_SENHA = ?',
    array($login, $senha)
);
$usuario = $stmtUsuario ? sqlsrv_fetch_array($stmtUsuario, SQLSRV_FETCH_ASSOC) : false;

if (!$usuario) {
    header('Location: login.php');
    exit;
}

$estado = isset($_GET['estado']) ? trim($_GET['estado']) : '';
$cidade = isset($_GET['cidade']) ? trim($_GET['cidade']) : '';
$bairro = isset($_GET['bairro']) ? trim($_GET['bairro']) : '';
$codMot = isset($_GET['codMot']) ? trim($_GET['codMot']) : '';

$rows = buscarRequisicoesPainel($conn, $StatusComMot, $SituacaoMotoristaAtivo, $estado, $cidade, $bairro, $codMot);
$opcoes = buscarOpcoesPainel($conn, $StatusComMot, $SituacaoMotoristaAtivo, $estado, $cidade, $bairro);
$motoristas = agruparRequisicoesPainel($rows);

function hPainel($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Requisições por motorista</title>
    <link rel="stylesheet" href="../assets/css/painel.css">
    <link rel="stylesheet" href="../assets/css/carregamento.css">
    <script src="../assets/JS/carregamento.js" defer></script>
    <script src="../assets/JS/filtros-painel.js" defer></script>
</head>
<body>
    <main class="painel-shell">
        <header class="painel-header">
            <div class="painel-identidade">
                <img src="../media/logo.png" alt="Databit">
                <div>
                    <span class="painel-eyebrow">Roteirização</span>
                    <h1>Requisições por motorista</h1>
                </div>
            </div>
            <a class="btn-logout" href="../models/logout.php" title="Sair do sistema">
                <span>Sair</span><img src="../media/logoff.png" alt="">
            </a>
        </header>

        <section class="filtros-card">
            <form method="get" action="painel.php" class="painel-filtros" id="filtrosPainel">
                <label>Estado
                    <select name="estado">
                        <option value="">Todos os estados</option>
                        <?php foreach ($opcoes['estados'] as $valor) { ?>
                            <option value="<?php echo hPainel($valor); ?>" <?php echo $estado === (string) $valor ? 'selected' : ''; ?>><?php echo hPainel($valor); ?></option>
                        <?php } ?>
                    </select>
                </label>
                <label>Cidade
                    <select name="cidade">
                        <option value="">Todas as cidades</option>
                        <?php foreach ($opcoes['cidades'] as $valor) { ?>
                            <option value="<?php echo hPainel($valor); ?>" <?php echo $cidade === (string) $valor ? 'selected' : ''; ?>><?php echo hPainel($valor); ?></option>
                        <?php } ?>
                    </select>
                </label>
                <label>Bairro
                    <select name="bairro">
                        <option value="">Todos os bairros</option>
                        <?php foreach ($opcoes['bairros'] as $valor) { ?>
                            <option value="<?php echo hPainel($valor); ?>" <?php echo $bairro === (string) $valor ? 'selected' : ''; ?>><?php echo hPainel($valor); ?></option>
                        <?php } ?>
                    </select>
                </label>
                <label>Motorista
                    <select name="codMot">
                        <option value="">Todos os motoristas</option>
                        <?php foreach ($opcoes['motoristas'] as $codigo => $nome) { ?>
                            <option value="<?php echo hPainel($codigo); ?>" <?php echo $codMot === (string) $codigo ? 'selected' : ''; ?>><?php echo hPainel($nome); ?></option>
                        <?php } ?>
                    </select>
                </label>
                <div class="filtros-acoes">
                    <button type="submit">Aplicar filtros</button>
                    <a href="painel.php">Limpar</a>
                </div>
            </form>
        </section>

        <?php if (count($motoristas) === 0) { ?>
            <section class="estado-vazio">Nenhuma requisição encontrada para os filtros selecionados.</section>
        <?php } else { ?>
            <section class="motoristas-grid">
                <?php foreach ($motoristas as $motorista) { ?>
                    <form class="motorista-form" action="index.php" method="post">
                        <input type="hidden" name="codMot" value="<?php echo hPainel($motorista['codigo']); ?>">
                        <?php if ($estado !== '') { ?><input type="hidden" name="Estado" value="<?php echo hPainel($estado); ?>"><?php } ?>
                        <?php if ($cidade !== '') { ?><input type="hidden" name="Cidade" value="<?php echo hPainel($cidade); ?>"><?php } ?>
                        <?php if ($bairro !== '') { ?><input type="hidden" name="Bairro" value="<?php echo hPainel($bairro); ?>"><?php } ?>
                        <input type="hidden" name="origem" value="painel">
                        <button class="motorista-card" type="submit">
                            <header class="motorista-card-header">
                                <div>
                                    <span class="motorista-codigo">Motorista <?php echo hPainel($motorista['codigo']); ?></span>
                                    <h2><?php echo hPainel($motorista['nome']); ?></h2>
                                </div>
                                <span class="abrir-painel">Roteirizar →</span>
                            </header>
                            <div class="motorista-indicadores">
                                <span class="indicador-verde"><strong><?php echo $motorista['total']; ?></strong> Requisições com motorista</span>
                            </div>
                            <div class="requisicoes-lista">
                                <?php foreach ($motorista['requisicoes'] as $requisicao) { ?>
                                    <article class="requisicao-item">
                                        <div class="requisicao-linha-principal">
                                            <strong>REQ <?php echo hPainel($requisicao['Req']); ?></strong>
                                            <span><?php echo hPainel($requisicao['NomeStatus']); ?></span>
                                        </div>
                                        <div class="requisicao-cliente"><?php echo hPainel($requisicao['NomeCli']); ?></div>
                                        <div class="requisicao-meta">
                                            <span>NF: <?php echo hPainel($requisicao['Nota']); ?></span>
                                            <span><?php echo hPainel($requisicao['DataAbertura']); ?></span>
                                        </div>
                                        <div class="requisicao-local"><?php echo hPainel($requisicao['Cidade']); ?> · <?php echo hPainel($requisicao['Bairro']); ?></div>
                                    </article>
                                <?php } ?>
                            </div>
                        </button>
                    </form>
                <?php } ?>
            </section>
        <?php } ?>
    </main>
</body>
</html>
