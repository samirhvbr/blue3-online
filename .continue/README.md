# `.continue/` do ONLINE — o que ainda é decisão

> **Reorganizado em 29/08/2026.** `.continue/` é **fila**; o produzido vira `docs/`.

## Onde está o quê

| Documento | O que é |
|---|---|
| [`../docs/ESTADO.md`](../docs/ESTADO.md) | **Comece aqui.** Estado medido em 24/08: o que existe, o que falta, e a decisão que precede o código |
| [`../docs/HISTORICO/SAMIR.md`](../docs/HISTORICO/SAMIR.md) · [`SAMIR-PROJETO.md`](../docs/HISTORICO/SAMIR-PROJETO.md) | Brief original. **Histórico, não fila** |

## A única coisa pendente é uma decisão sua

**O ONLINE vira monitor de verdade, ou fica painel de incidentes manual?**

O site está no ar (`blue3.online` → 200), mas das quatro peças do brief — sonda externa,
alvo de ping, medição e alerta para a INTRANET — **nenhuma existe**. Busca por modelo e
por migration, ambas vazias.

⚠️ **O brief tem tom de "quase pronto" e não deve ser lido como fila.** Quem abrir
`SAMIR.md` achando que falta pouco vai se enganar: falta a metade que define o produto.
Por isso ele saiu daqui.

---

## 📐 Convenção de documentação da frota

Este repo segue a convenção única da casa: **`.continue/` é fila, `docs/` é o que já
foi produzido.** Regra completa, com o gatilho de quando um vira o outro, o padrão de
nome que carrega estado, o gate de deploy e a diferença entre roteiro `.md` e
diagnóstico `.sh`:

**→ `BLUE3-INTRANET/docs/CONVENCAO-DOCUMENTACAO.md`** *(fonte única — não copiar o
conteúdo para cá; cópia vira segunda fonte de verdade)*
