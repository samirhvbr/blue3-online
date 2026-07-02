# Blue3 Online

Public **service status** page for Blue3 + an admin panel to
manage components and incidents.

- **Stack:** Laravel 12 (PHP 8.x), Blade, Tailwind CSS.
- **Pages:** `/` (public status) · `/admin` (panel).

## 🔄 Before you start: `git pull`

**ALWAYS** check for remote updates before writing or changing anything in this repository:

```bash
git pull          # already pre-authorized (allow)
```

Working on top of an outdated base causes conflicts. Pull first, always. To just inspect beforehand: `git fetch && git status`.

## Version (`version.md`)

The version lives in **`version.md`** at the project root (tracked in git) and is read via
`config('app.version')` — shown in the **footer** of the public status page and the admin
panel. If the file does not exist, it falls back to `APP_VERSION` (env) and, finally, `0.0.0`.

Format **`X.Y.Z`** (same convention as INTRANET):

- **X** — final stable version (manual change).
- **Y** — significant structural change — from 0 to 99.
- **Z** — automatic increment: new screen, new database table, layout change or
  button rename → `Z+1` — from 0 to 999.

**How to increment:** edit `version.md` (e.g., `0.1.0` → `0.1.1`) in the **same commit** as the change.

## Commits

Always in the format **`<version> - <comment>`**, using the current version from `version.md`
(the same standard as INTRANET — version before the comment). Examples:

```
0.1.1 - Status: card de incidente com severidade
0.2.0 - Painel: CRUD de POPs
```

> Write commits with a good description. The push is optional; the organized, documented commit
> is mandatory.
