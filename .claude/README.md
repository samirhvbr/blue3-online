# Configuração Claude Code — Blue3 (`~/x/.claude`)

Config usada **quando o Claude Code é iniciado com o diretório de trabalho em `~/x`**
(a pasta que agrupa os projetos). Não afeta sessões abertas dentro de um projeto
específico — aí valem o `.claude/` do projeto + o `~/.claude/` global.

## Arquivos

| Arquivo | Papel |
|---------|-------|
| `settings.json` | Config **ativa** (versionável): permissões e `effortLevel`. **Não escolhe modelo.** |
| `settings.local.json` | Override local (gitignored). Acumula `allow` por sessão e **tem precedência** sobre o `settings.json`. |

**Trocar de modelo:** `/model`, na sessão — não existe arquivo pra copiar por cima do
`settings.json`. Os templates stand-by `json-opus`, `json-fable5-opus` e
`json-fable5-opus-sonnet` foram removidos em 24/09/2026 (ADR-027 do repodocs).

> O `settings.local.json` tem precedência sobre o `settings.json` no que ambos
> declaram — permissões e `env`. Modelo nenhum dos dois declara.

## Modelo — escolha do usuário, e este repositório não a faz

**O modelo se escolhe com `/model`, por sessão.** Este repositório não escolhe nenhum:
o `settings.json` não tem `model`, `fallbackModel` nem `availableModels`, e o `env` não
carrega `ANTHROPIC_MODEL`, `ANTHROPIC_DEFAULT_*_MODEL` nem `CLAUDE_CODE_SUBAGENT_MODEL`.
**Subagente herda o modelo da sessão** — a ordem do Claude Code é modelo da chamada >
`model` na definição do agente > `CLAUDE_CODE_SUBAGENT_MODEL` > modelo da sessão; sem
nada definido, o subagente roda no modelo da sessão. Agente próprio em `.claude/agents/`
diz `model: inherit`, ou não diz nada. Regra da casa: **ADR-027 do repodocs**
(24/09/2026).

**Por que não pinar:** todo pin desses sobreviveu ao modelo que pinava. `opus[1m]`
segurava a sessão na Opus anterior — a variante 1M só existia nela; os perfis stand-by
prendiam Opus 4.8 / Fable 5 / Sonnet 4.6; `CLAUDE_CODE_SUBAGENT_MODEL=opus` mandava
subagente pra Opus 5 enquanto a sessão já rodava a 5.5. E pin escrito aqui não vence os
settings gerenciados da organização, que só um Owner edita.

### Regras que vale lembrar
- **Effort `max` vai por env** (`CLAUDE_CODE_EFFORT_LEVEL=max`). O campo `effortLevel`
  do JSON **só aceita** `low/medium/high/xhigh` — `max` ali é ignorado silenciosamente.
- **Janela de contexto é característica do modelo**, não deste repositório: depende do
  que você escolher em `/model`. Onde 1M é nativo (API Anthropic), vale sem flag; para
  desligar bastaria `CLAUDE_CODE_DISABLE_1M_CONTEXT=1` (não usamos). No plano Max é
  incluso, zero créditos — **usar longe do limite**.
- **Fable 5 / créditos:** incluso no Max até ~22/jun/2026; depois consome créditos
  (~$10/$50 por MTok, 2× o Opus). Requer Claude Code v2.1.170+. Confirme antes de rodar pesado.

## Permissões

- `defaultMode: plan`.
- **deny:** `rm -rf`, `git push --force/-f`, `git reset --hard`, `git clean -fd`,
  `migrate:fresh/reset`, `db:wipe`, `mysql`/`mariadb` diretos, `curl|sh`/`wget|sh`,
  e leitura de `auth.json` / chaves OAuth / `*.pem`.
- **ask (confirma):** `sudo`, `migrate`, `composer require/update`,
  `npm install --save`, `mysqldump`/`mariadb-dump`.
- **allow:** read/edit/write, git read-only + `add`/`commit`/`push`, artisan
  não-destrutivo, `php -l`, composer/npm/build, linters e testes.

> `mysql`/`mariadb` diretos são bloqueados de propósito: o Claude deve **gerar
> migrations**, nunca rodar SQL na mão.
