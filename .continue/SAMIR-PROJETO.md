# BLUE3 ONLINE — Página de Status (`blue3.online`)

> Doc de demandas/continuidade. Expande o brain-dump em [`SAMIR.md`](./SAMIR.md).
> Iniciado em **24/06/2026**. Estado: **greenfield** (repo recém-criado, sem stack definida).

## O que é

Página de **status público** da Blue3 (provedor de internet / ISP), rodando em
**https://blue3.online**, no estilo da **https://status.claude.com/**: mostra em tempo
quase-real a saúde dos POPs e serviços, com indicador por componente (operacional /
degradado / fora), histórico de incidentes e uptime.

**Por quê:** a Blue3 é ISP. Cliente e equipe precisam ver de imediato se há POP/serviço
com problema. Transparência pública + menos chamados no suporte + um sinal único de
saúde que outros sistemas (INTRANET) consomem.

## Arquitetura proposta

```
   [Sondas externas / Debian leve]          (medem a Blue3 "de fora")
        | ping/ICMP, perda, latência aos POPs
        v  push HTTPS autenticado por token
   [Central blue3.online]  ──► página pública (estilo status.claude.com)
        |  agrega medições, calcula status, guarda histórico/uptime
        ├─► /admin  (CRUD de sondas e de alvos/pings; gestão de incidentes)
        └─► API de status ──► INTRANET (mostra alerta quando algo cai)
```

1. **Sondas externas (probes).** Servidores Debian leves, em redes externas, que medem a
   Blue3 de fora (ping aos POPs; depois latência/perda, HTTP check, etc.). Provisionadas
   por **script bash**. Reportam para a central via API. Várias sondas em locais distintos
   dão **consenso** e evitam falso-positivo de uma rede ruim isolada.
2. **Central (`blue3.online`).** Recebe e agrega as medições, calcula o status por
   componente, persiste histórico (uptime, incidentes) e serve a página pública.
3. **Admin (`/admin`).** Login + CRUD de sondas e de alvos de ping (POPs/serviços).
4. **Integração INTRANET.** A central expõe o estado; a INTRANET exibe alerta quando algo
   está com problema.

## Demandas (do `SAMIR.md`, detalhadas)

### 1. Página pública
- Estilo status.claude.com: lista de componentes (POPs + serviços) com indicador
  verde/amarelo/vermelho, uptime %, timeline de incidentes (ex. 90 dias), banner de
  incidente ativo.
- Atualização near-real-time (auto-refresh/polling no MVP; WebSocket depois, se valer).

### 2. Sondas externas
- **MVP = ping** aos POPs. Arquitetura extensível para latência/perda, HTTP, traceroute/MTR, DNS depois.
- Reportam por **push HTTPS** autenticado (sondas costumam estar atrás de NAT → push é mais simples que pull).
- Cada sonda tem **token próprio** + identificação de local.
- **Script bash** para provisionar/rodar a sonda num Debian leve (cron/systemd timer fazendo ping → POST).

### 3. Central / backend
- Ingestão autenticada das medições.
- **Cálculo de status** por componente a partir do consenso das sondas (regra a definir:
  ex. X% das sondas falhando por N ciclos → degradado; mais que isso → fora).
- Detecção automática de incidente + incidente/manutenção **manual** pelo admin.
- Histórico para uptime e timeline.

### 4. Admin (`/admin`)
- Login. Usuário de bootstrap: **`samir@blue3.com.br`** / senha inicial **`blue3samir123`**.
  - ⚠️ **Segurança:** credencial só de bootstrap — **trocar no 1º acesso** e **nunca
    hardcodar** em produção. A Blue3 já teve essa correção em outro projeto (SITE removeu
    senhas hardcoded). Seedar via env/migration, hash bcrypt, forçar troca.
- CRUD de **sondas** (cadastro, token, local, ativa/inativa).
- CRUD de **alvos/pings** (POP ou serviço: host/IP, tipo de check, intervalo, threshold).
- (Futuro) gestão de incidentes/manutenções e de usuários.

### 5. Integração com a INTRANET
- A INTRANET (`adm.blue3.com.br` / `sys.blue3.cloud`, Laravel 12) mostra **alerta** quando a
  ONLINE detecta problema — reaproveitar o padrão de alerta que a INTRANET já tem
  ("alerta de tela parada" já existe lá).
- Formato a definir: a ONLINE expõe `GET /api/status` que a INTRANET consome (pull) **ou**
  empurra um webhook/evento na INTRANET (push). Pull é mais simples para começar.

## Decisões em aberto (precisam do Samir)

| # | Decisão | Recomendação inicial |
|---|---------|----------------------|
| 1 | ~~**Stack da central/admin**~~ ✅ | **Laravel + MySQL/MariaDB** — encaminhada em 24/06: o `deploy.sh` foi criado no padrão Laravel do WCUP/INTRANET (artisan/composer/npm), confirmando a direção. Falta só o bootstrap da app. |
| 2 | **Protocolo sonda→central** | **Push** HTTPS autenticado por token (sondas atrás de NAT). |
| 3 | **Integração INTRANET** | INTRANET consome `GET /api/status` da ONLINE (pull); webhook depois, se precisar de tempo-real. |
| 4 | **Hospedagem** | Onde roda `blue3.online`? Onde ficam as sondas? (idealmente fora da rede Blue3) |
| 5 | **Domínio/DNS/TLS** | `blue3.online` já registrado? Emitir certificado. |
| 6 | **Política de status** | Definir thresholds: nº de sondas / nº de ciclos para marcar degradado vs fora. |

## Estado atual (24/06/2026)

- Repo **greenfield**: `git@github.com:BLUE3-ISP/ONLINE.git`, branch `master`, 2 commits
  (`9c20a3a first commit`, `f5f2b4b Start Default`). README só `# ONLINE`.
- `.claude/` já configurado no padrão Blue3 (perfis Opus/Fable, permissões) e versionado.
- **Commit `c1d6c89`**: `deploy.sh` (padrão WCUP/INTRANET, alvo `/srv/www/blue3.online`) + `.continue/`.
- **Laravel 12** instalado (Tailwind v4 + Vite, igual ao WCUP). `.env` em MySQL + drivers `file`/`sync` → roda local **sem banco**.
- **Página pública `/`** estilo status.claude.com **pronta e validada** (HTTP 200, `npm run build` OK, screenshot conferido): banner geral, componentes (POPs/Serviços) com pílula de status + barras de uptime 90 dias, histórico de incidentes. Dados **mock** em `app/Services/StatusService.php` (`app/Enums/ComponentStatus.php`, `StatusController`, `resources/views/status/index.blade.php`).
- **A app Laravel + a página ainda NÃO estão commitadas** (aguardando review).

## Próximos passos sugeridos

1. ~~Fechar a decisão de stack (#1)~~ ✅ Laravel (via `deploy.sh`).
2. ~~Bootstrap da app Laravel + página pública estilo status.claude.com (mock)~~ ✅ feito (24/06).
3. **Modelo de dados:** sondas, alvos, medições, incidentes.
4. **API de ingestão** (sonda→central) com auth por token.
5. **Script bash da sonda** (ping → POST), provisionável em Debian leve.
6. **Admin `/admin`**: login + CRUD de sondas e alvos.
7. **Cálculo de status** + histórico/uptime + timeline de incidentes.
8. **Integração INTRANET** (alerta).
9. **Endurecer auth**: trocar senha de bootstrap, sem hardcode.

## Referências

- Modelo visual: https://status.claude.com/
- Projeto irmão p/ integração: `BLUE3/INTRANET` (Laravel 12) — ver `BLUE3/.continue/README_20260623.md`.
- Convenções/perfis: `BLUE3/ONLINE/.claude/README.md` e `CLAUDE.md` da Blue3.
