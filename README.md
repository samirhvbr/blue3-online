# Blue3 Online

Página pública de **status dos serviços** da Blue3 + painel administrativo para
gerenciar componentes e incidentes.

- **Stack:** Laravel 12 (PHP 8.x), Blade, Tailwind CSS.
- **Páginas:** `/` (status público) · `/admin` (painel).

## 🔄 Antes de começar: `git pull`

**SEMPRE** verifique atualizações remotas antes de escrever ou alterar qualquer coisa neste repositório:

```bash
git pull          # já está pré-autorizado (allow)
```

Trabalhar sobre uma base desatualizada gera conflitos. Puxe primeiro, sempre. Para só inspecionar antes: `git fetch && git status`.

## Versão (`version.md`)

A versão fica em **`version.md`** na raiz do projeto (versionado no git) e é lida via
`config('app.version')` — exibida no **rodapé** da página pública de status e do painel
admin. Se o arquivo não existir, cai para `APP_VERSION` (env) e, por fim, `0.0.0`.

Formato **`X.Y.Z`** (mesma convenção do INTRANET):

- **X** — versão estável final (alteração manual).
- **Y** — mudança estrutural significativa — de 0 a 99.
- **Z** — incremento automático: nova tela, nova tabela no banco, mudança de layout ou
  renomear botão → `Z+1` — de 0 a 999.

**Como incrementar:** edite `version.md` (ex.: `0.1.0` → `0.1.1`) no **mesmo commit** da mudança.

## Commits

Sempre no formato **`<versão> - <comentário>`**, usando a versão atual do `version.md`
(o mesmo padrão do INTRANET — versão antes do comentário). Exemplos:

```
0.1.1 - Status: card de incidente com severidade
0.2.0 - Painel: CRUD de POPs
```

> Faça commits com boa descrição. O push é opcional; o commit organizado e documentado
> é obrigatório.
