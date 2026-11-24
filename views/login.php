<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#dceef4">
  <title>Acesso | Databit</title>
  <link rel="stylesheet" href="../css/EstiloLogin.css">
  <link rel="stylesheet" href="../assets/css/login-moderno.css">
</head>
<body class="login-page">
  <main class="login-shell">
    <section class="login-card" aria-label="Acesso ao sistema">
      <div class="login-logo-area">
        <img src="../media/logo.png" class="login-logo" alt="Databit">
      </div>

      <form class="login-form" action="../models/loginExec.php" method="post">
        <label class="sr-only" for="login">Usuário</label>
        <input type="text" id="login" name="login" placeholder="Usuário" autocomplete="username" required autofocus>

        <label class="sr-only" for="password">Senha</label>
        <input type="password" id="password" name="password" placeholder="Senha" autocomplete="current-password" required>

        <button type="submit">Entrar</button>
      </form>
    </section>
  </main>
</body>
</html>
