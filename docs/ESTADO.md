# ONLINE (blue3.online) — estado medido e o que falta

> **Medido em:** 24/08/2026, contra o código e contra o site no ar.
> **Decisão do Samir (24/08):** *"preciso dar atenção a isso, vamos documentar em `.continue`
> e vamos revisar."* Este arquivo é esse registro.
>
> Os três docs anteriores (`SAMIR.md`, `SAMIR-PROJETO.md`, `samir2.md`) são o **brief
> original** e continuam valendo como intenção. Este aqui é o **estado**.

## O resumo em uma frase

O site **está no ar** (`https://blue3.online` → 200, `<title>Status — Blue3</title>`), mas
é um **painel de incidentes alimentado à mão**. O que faz dele um *monitor* — as sondas —
**não existe em nenhuma linha de código**.

## Medido: o que existe

| | |
|---|---|
| Modelos | `Component`, `ComponentGroup`, `Incident`, `User` |
| Tabelas | `users`, `cache`, `jobs`, `component_groups`, `components`, `incidents` |
| Rotas | `/` (pública), `/admin/login`, `/admin/logout`, `components`, `incidents` |
| No ar | ✅ `blue3.online` responde 200; deploy em `/srv/www/online.blue3`, já no orquestrador |

## Medido: o que o brief pede e **não existe**

Busca por modelo *e* migration, ambos vazios:

| Conceito do brief | No código |
|---|---|
| **Probe / Sonda** (servidor Debian externo que mede a Blue3 "de fora") | ❌ **não existe** |
| **Target / alvo de ping** (os POPs a monitorar) | ❌ **não existe** |
| **Ping / medição** (latência, perda) | ❌ **não existe** |
| **Alert** (o que a INTRANET consome) | ❌ **não existe** |

Ou seja: das quatro peças do brief (`SAMIR.md`), **nenhuma** foi construída.

```
brief: [Sondas externas] --ping/ICMP--> [POPs]
              |
              v
       [blue3.online] --/admin CRUD de sondas e alvos--> ❌ não existe
              |
              +--> [API de status] --> [INTRANET mostra alerta] --> ❌ não existe
```

## A decisão que precede o código

**Isto vira monitor de verdade, ou fica painel manual?** As duas respostas são legítimas e
têm custos muito diferentes:

- **Fica painel manual** — o produto é uma página de status editada pela equipe quando algo
  cai. Já funciona. Custo zero, e o brief original passa a ser histórico, não pendência.
- **Vira monitor** — é trabalho novo de verdade: 4 tabelas, um agente de sonda para instalar
  em máquinas externas, um canal autenticado sonda→servidor, retenção de série temporal, e
  o contrato de alerta com a INTRANET. Não é "terminar" o que existe; é começar a metade que
  falta.

Enquanto a decisão não vem, **o brief não deve ser lido como fila**: quem abrir `SAMIR.md`
achando que falta pouco vai se enganar pelo tom de "quase pronto".

## Se for virar monitor — ordem sugerida

Nesta ordem porque cada passo torna o seguinte verificável:

1. **Modelar `probes` e `targets`** + CRUD no `/admin`. Sem isso não há o que a sonda
   consulte, e o resto não tem onde apoiar.
2. **Canal sonda→servidor autenticado** (token por sonda, não senha compartilhada) e o
   endpoint de ingestão de medição. Decidir aqui a **retenção**: ping de minuto em minuto
   vira série temporal grande rápido.
3. **Script de instalação da sonda** em Debian leve — o brief pede explicitamente. É o que
   permite testar com uma sonda real em vez de dado sintético.
4. **Derivar o estado do componente** a partir das medições (hoje `Component.status` é
   manual) — este é o passo que muda o produto.
5. **Contrato de alerta com a INTRANET**, por último: só faz sentido quando o estado é
   confiável. Alerta em cima de dado ruim treina a equipe a ignorar alerta.

⚠️ **Nada disso está começado.** Nenhuma migration, nenhum modelo, nenhuma rota.
