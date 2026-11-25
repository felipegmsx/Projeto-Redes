-- Grupo 05 Academia. Execute no DBeaver na conexão do usuário demonstracao.
SELECT id, aluno, plano, criado_em
  FROM academia.alunos
  ORDER BY id DESC
  LIMIT 15;

SELECT ID, post_title, post_status
  FROM wordpress.wp_posts
  WHERE post_type = 'post' AND post_status = 'publish'
  ORDER BY ID DESC
  LIMIT 15;

SHOW SESSION STATUS LIKE 'Ssl_cipher';
