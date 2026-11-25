#!/bin/sh

# Este arquivo também funciona quando o entrypoint do MySQL o importa.
senha_valida() {
    case "$1" in
        ''|*[!a-zA-Z0-9]*) return 1 ;;
    esac
    [ "${#1}" -ge 32 ]
}

if ! senha_valida "${ACADEMIA_DB_PASSWORD:-}" || ! senha_valida "${DEMO_DB_PASSWORD:-}"; then
    echo 'Use senhas alfanuméricas de pelo menos 32 caracteres.' >&2
    return 1 2>/dev/null || exit 1
fi

MYSQL_PWD="$MYSQL_ROOT_PASSWORD" mysql --protocol=socket --user=root <<SQL
CREATE USER IF NOT EXISTS 'academia_app'@'%' IDENTIFIED BY '$ACADEMIA_DB_PASSWORD';
GRANT SELECT, INSERT ON academia.alunos TO 'academia_app'@'%';

CREATE USER IF NOT EXISTS 'demonstracao'@'%' IDENTIFIED BY '$DEMO_DB_PASSWORD';
GRANT SELECT ON academia.* TO 'demonstracao'@'%';
GRANT SELECT ON wordpress.* TO 'demonstracao'@'%';
SQL
