# Changelog

Entradas no formato da mensagem de commit (`versão - descrição curta em
português`), mais recente no topo. **Cada cabeçalho `##` é literalmente o
assunto do commit** — a entrada é escrita *antes* do commit, para que a frase
que cai no `git log` seja uma frase pesada e não improvisada na hora.

Corpos são narrativos: o que mudou, por quê e o que foi medido. Este arquivo
não se reescreve.

> **O registro começa aqui.** Este arquivo nasceu depois do repositório: as
> versões anteriores estão no `git log` e não foram retroalimentadas, porque
> reconstruí-las agora produziria uma história plausível, não uma verdadeira.


## 0.1.18 - os blocos de eco são regenerados a partir do repodocs

As regras marcadas de `CLAUDE.md` e `AGENTS.md` são reescritas a partir da
fonte única em [samirhvbr/repodocs](https://github.com/samirhvbr/repodocs):
`QUEUE-RULE`, `RELEASES-RULE`, `LANGUAGE-RULE`, `COMMIT-RULE` e `CICD-RULE`.
Bloco é substituído por inteiro entre os marcadores, cabeçalho incluído — é isso
que faz uma edição local não sobreviver e não confundir a próxima leitura.

O `QUEUE-RULE` é novo e chega pela primeira vez: a `.continue/` guarda trabalho
que ainda não existe, e um documento só sai dela quando a coisa que ele descreve
**existe**. Tamanho, idioma e desalinho não são condição de saída. **Nunca
esvazie essa pasta como arrumação.**

## 0.1.17 - as permissoes seguem o repodocs: cinco comandos vao para o ask, sete regras saem do deny

`rm -rf` e `curl`/`wget` com pipe para shell saem do `deny` e passam a pedir confirmacao
(`ask`). A leitura de `.env`/`.env.*`, `git push --force`/`-f`, `git reset --hard` e
`git clean -fd` saem do `deny`. As leituras de chave (`*.pem`, `*.key`, `*.p8`, `*.p12`,
`*.pfx`) continuam bloqueadas. Decisao do dono em 24/09/2026, replicada do repodocs 1.17.0
(ADR-028).

## 0.1.16 - o repositorio deixa de escolher o modelo

`CLAUDE_CODE_SUBAGENT_MODEL` sai do `.claude/settings.json`. O modelo passa a ser escolha
do usuario, feita com `/model`, e o subagente herda: por padrao o Claude Code da ao
subagente o modelo da sessao, e essa variavel era a unica coisa que o fazia diferente —
com a sessao na Opus 5.5, os subagentes foram medidos na Opus 5, porque a variavel nomeia o
alias `opus` e o alias ainda passa pelo pin das configuracoes gerenciadas da organizacao.

Os 3 perfis stand-by (`json-fable5-opus`, `json-fable5-opus-sonnet`, `json-opus`) saem junto: existiam so para trocar o modelo sendo copiados
por cima do `settings.json`, e fixavam modelos que o catalogo ja deixou para tras. Para
recuperar um: `git show <este commit>^:.claude/<nome>`.

`.claude/README.md` deixa(m) de descrever um perfil de modelo.

Regra e medicao: ADR-027 do repodocs.

Sem teste: configuracao e documentos. Conferido que o arquivo parseia e que a checagem
do runbook §7 do repodocs fica em silencio aqui.

## 0.1.15 - o pin de modelo sai do .claude/settings.json

Saem `"model": "opus[1m]"` e o pin de ambiente `ANTHROPIC_DEFAULT_OPUS_MODEL`.
O sufixo de janela era pin de versao disfarçado — a variante 1M so existia na Opus
anterior, entao toda sessao nascia nela enquanto o catalogo ja oferecia a mais nova.
A variavel de ambiente e pior que um pin: redefine o que `opus` significa para tudo
que le, inclusive o seletor de modelo.

Nao desbloqueia nada sozinho: a camada que decide e a dos settings gerenciados pela
conta, que vencem qualquer arquivo local. Regra, medicao e o que escrever no lugar
(`"model": "opus55"`, versao nomeada): ADR-026 do repodocs.

Sem teste: duas chaves de JSON e um comentario. Conferido que o arquivo ainda parseia.

## 0.1.14 - os hooks de git são regenerados a partir do repodocs

Os dois hooks do padrão são reescritos a partir do repodocs, e o
`tools/release.sh` junto quando ele veio de lá. `commit-msg` confere a forma do
assunto (`X.Y.Z - descrição`), recusa prefixo de Conventional Commits e
mensagem vaga, **e confere que o `X.Y.Z` do assunto é a versão que este commit
carrega no `version.md`**. `pre-push` compara o `version.md` local com o do
branch padrão no remoto — versão repetida e versão que anda para trás — **só
quando o push realmente atualiza aquele branch**, de modo que deleção de branch,
tag e branch de tópico passam.

O hook **não confere idioma** e não teria como: o que ele mede é a forma e o
número.

Válvula de escape declarada nos dois: `REPODOCS_NO_HOOK=1`. Num clone novo, ligue com
`git config core.hooksPath tools/git-hooks`.

## 0.1.13 - os blocos de eco são regenerados a partir do repodocs

As quatro regras marcadas de `CLAUDE.md` e `AGENTS.md` são reescritas a partir
da fonte única em [samirhvbr/repodocs](https://github.com/samirhvbr/repodocs):
`QUEUE-RULE`, `RELEASES-RULE`, `LANGUAGE-RULE` e `COMMIT-RULE`. Bloco é
substituído por inteiro entre os marcadores, cabeçalho incluído — é isso que faz
uma edição local não sobreviver e não confundir a próxima leitura.

O `QUEUE-RULE` é novo e chega pela primeira vez: a `.continue/` guarda trabalho
que ainda não existe, e um documento só sai dela quando a coisa que ele descreve
**existe**. Tamanho, idioma e desalinho não são condição de saída. **Nunca
esvazie essa pasta como arrumação.**
