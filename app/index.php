<?php
declare(strict_types=1);

date_default_timezone_set('America/Fortaleza');
header('Cache-Control: no-store');

const PLANOS = ['Mensal', 'Trimestral', 'Anual'];

function escapar(string $valor): string
{
    return htmlspecialchars($valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function lerCampo(string $campo): string
{
    $valor = $_POST[$campo] ?? '';
    return is_string($valor) ? trim($valor) : '';
}

$aluno = '';
$plano = '';
$aviso = '';
$erro = '';
$registros = [];

try {
    $host = getenv('ACADEMIA_DB_HOST');
    $banco = getenv('ACADEMIA_DB_NAME');
    $pdo = new PDO(
        "mysql:host=$host;port=3306;dbname=$banco;charset=utf8mb4",
        getenv('ACADEMIA_DB_USER'),
        getenv('ACADEMIA_DB_PASSWORD'),
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    $pdo->exec("SET time_zone = '-03:00'");

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $aluno = lerCampo('aluno');
        $plano = lerCampo('plano');

        if (preg_match('/\A[^\p{C}]{1,100}\z/u', $aluno) !== 1 || !in_array($plano, PLANOS, true)) {
            http_response_code(422);
            $erro = 'Informe um nome de até 100 caracteres e selecione um plano.';
        } else {
            $inserir = $pdo->prepare('INSERT INTO alunos (aluno, plano) VALUES (:aluno, :plano)');
            $inserir->execute(['aluno' => $aluno, 'plano' => $plano]);
            header('Location: /?cadastro=ok', true, 303);
            exit;
        }
    }

    $listar = $pdo->prepare('SELECT id, aluno, plano, criado_em FROM alunos ORDER BY id DESC');
    $listar->execute();
    $registros = $listar->fetchAll();

    if (($_GET['cadastro'] ?? '') === 'ok') {
        $aviso = 'Cadastro concluído. Confira o novo aluno na listagem.';
    }
} catch (PDOException $e) {
    http_response_code(503);
    error_log('Academia: erro de acesso ao banco, SQLSTATE ' . $e->getCode());
    $erro = 'O banco de dados está indisponível. Tente novamente em instantes.';
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="color-scheme" content="light">
  <title>Grupo 05 — Academia | Cadastro de alunos</title>
  <link rel="stylesheet" href="/style.css">
</head>
<body>
<header class="barra">
  <div class="largura cabeçalho">
    <span class="marca"><span class="sinal" aria-hidden="true"></span>Grupo 05 <span class="marca-divisor">/</span> Academia</span>
    <a href="http://blog.academia.local:9005/">Blog da academia</a>
  </div>
</header>
<main class="largura">
  <header class="abertura">
    <h1>Cadastro de alunos</h1>
    <p>Associe um aluno a um plano e consulte os cadastros.</p>
  </header>
  <?php if ($erro !== ''): ?><p class="mensagem erro" role="alert"><?= escapar($erro) ?></p><?php endif ?>
  <?php if ($aviso !== ''): ?><p class="mensagem sucesso" role="status"><?= escapar($aviso) ?></p><?php endif ?>
  <section class="cartao" aria-labelledby="novo-cadastro">
    <h2 id="novo-cadastro">Novo cadastro</h2>
    <form action="/" method="post">
      <div class="campo">
        <label for="aluno">Aluno</label>
        <input id="aluno" name="aluno" type="text" placeholder="Nome completo" maxlength="100" value="<?= escapar($aluno) ?>" required>
      </div>
      <div class="campo plano">
        <label for="plano">Plano</label>
        <select id="plano" name="plano" required>
          <option value="">Selecione um plano</option>
          <?php foreach (PLANOS as $opcao): ?>
            <option value="<?= escapar($opcao) ?>" <?= $plano === $opcao ? 'selected' : '' ?>><?= escapar($opcao) ?></option>
          <?php endforeach ?>
        </select>
      </div>
      <button type="submit">Cadastrar aluno</button>
    </form>
  </section>
  <section class="registros" aria-labelledby="lista">
    <div class="lista-titulo">
      <h2 id="lista">Alunos cadastrados</h2>
      <span><?= count($registros) ?> <?= count($registros) === 1 ? 'cadastro' : 'cadastros' ?></span>
    </div>
    <?php if ($registros !== []): ?>
      <div class="tabela">
        <table>
          <thead><tr><th scope="col">Aluno</th><th scope="col">Plano</th><th scope="col">Data e hora do cadastro</th></tr></thead>
          <tbody>
          <?php foreach ($registros as $registro): ?>
            <tr>
              <td><?= escapar($registro['aluno']) ?></td>
              <td><span class="etiqueta"><?= escapar($registro['plano']) ?></span></td>
              <td><?= escapar((new DateTimeImmutable($registro['criado_em']))->format('d/m/Y H:i:s')) ?></td>
            </tr>
          <?php endforeach ?>
          </tbody>
        </table>
      </div>
    <?php elseif ($erro === '' || http_response_code() !== 503): ?>
      <p class="vazio">Nenhum aluno cadastrado.</p>
    <?php endif ?>
  </section>
</main>
<footer class="largura rodape">
  <span>Grupo 05 — Academia</span>
  <span><time datetime="<?= date(DATE_ATOM) ?>"><?= date('d/m/Y H:i:s') ?></time> BRT</span>
</footer>
</body>
</html>
