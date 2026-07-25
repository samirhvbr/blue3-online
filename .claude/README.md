# Configuração Claude Code — Blue3 (`~/x/.claude`)

Config usada **quando o Claude Code é iniciado com o diretório de trabalho em `~/x`**
(a pasta que agrupa os projetos). Não afeta sessões abertas dentro de um projeto
específico — aí valem o `.claude/` do projeto + o `~/.claude/` global.

## Arquivos

| Arquivo | Papel |
|---------|-------|
| `settings.json` | Perfil **ativo** (versionável). Hoje = Opus-only. |
| `settings.local.json` | Override local (gitignored). Acumula `allow` por sessão e **tem precedência** sobre o `settings.json`. |
| `json-opus` | Template stand-by — **Opus-only**. |
| `json-fable5-opus` | Template stand-by — **Fable 5** + fallback Opus. |
| `json-fable5-opus-sonnet` | Template stand-by — **Fable 5** + fallback Opus → Sonnet. |

**Trocar de perfil:** copie o template por cima do ativo e reinicie o Claude Code.

```bash
cp json-fable5-opus settings.json   # ex.: passa a usar Fable 5
```

> O `settings.local.json` tem precedência. Pra um perfil valer 100%, ajuste também o
> bloco `env` dele (ou apague-o pra recriar do zero).

## Perfis de modelo

Os três templates têm o **mesmo bloco de permissões**; mudam só `model` / `fallbackModel` / `env`:

|  | Opus-only | Fable 5 + Opus | Fable 5 + Opus→Sonnet |
|--|-----------|----------------|------------------------|
| `model` | `opus` | `fable` | `fable` |
| `fallbackModel` | — | `["opus"]` | `["opus","sonnet"]` |
| Subagentes (`CLAUDE_CODE_SUBAGENT_MODEL`) | `opus` | `fable` | `fable` |
| Effort `max` | `CLAUDE_CODE_EFFORT_LEVEL=max` | idem | idem |
| Janela 1M | nativa (Opus 5) | nativa (Fable 5) | nativa (Fable 5) |
| Adaptive thinking | OFF | sempre ON¹ | sempre ON¹ |

¹ No Fable 5 o thinking é sempre adaptativo; `CLAUDE_CODE_DISABLE_ADAPTIVE_THINKING` não tem efeito.

### Regras que vale lembrar
- **Effort `max` vai por env** (`CLAUDE_CODE_EFFORT_LEVEL=max`). O campo `effortLevel`
  do JSON **só aceita** `low/medium/high/xhigh` — `max` ali é ignorado silenciosamente.
- **1M é nativo** no Opus 5 e no Fable 5 (API Anthropic), sem flag. Para desligar
  bastaria `CLAUDE_CODE_DISABLE_1M_CONTEXT=1` (não usamos). No plano Max é incluso,
  zero créditos — **usar longe do limite**.
- **Fable 5 / créditos:** incluso no Max até ~22/jun/2026; depois consome créditos
  (~$10/$50 por MTok, 2× o Opus). Requer Claude Code v2.1.170+. Confirme antes de rodar pesado.

## Permissões (mesmo bloco em todos os perfis)

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

> **Pendência:** os três `json-*` ainda têm `Bash(git push:*)` repetido em `allow` **e**
> `ask`. Como `ask` vence `allow`, neles o push **pede confirmação** — diferente do
> `settings.json` ativo, onde está só em `allow` (sobe direto). Pra alinhar, remova a
> linha `"Bash(git push:*)"` do array `ask` dos três templates (o Claude não edita
> regras de permissão em `.claude/` por segurança — faça à mão ou via `/update-config`).
