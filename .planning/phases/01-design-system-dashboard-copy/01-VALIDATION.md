# Phase 1 Validation — Design System + Dashboard + Copy

Aggregated automated verify commands from all three PLAN.md files.

---

## Validation Table

| Task ID | Description | Automated Verify Command | Expected Output | Requirements Covered |
|---------|-------------|--------------------------|-----------------|----------------------|
| PLAN-01 / Task 1 | Alpine.js instalado e loadingModal registrado | `node -e "const pkg = require('./package.json'); if (!pkg.dependencies?.alpinejs) { process.exit(1); } console.log('alpinejs OK:', pkg.dependencies.alpinejs)"` | `alpinejs OK: ^3.x.x` (versão semântica) | DESIGN-03 |
| PLAN-01 / Task 2 | Zero referências --vscode-* restantes após migração de CSS vars | `grep -c "vscode" resources/css/app.css resources/views/career/index.blade.php resources/views/resumes/print.blade.php` | `0` para cada arquivo | DESIGN-01, DESIGN-04 |
| PLAN-02 / Task 1 | flex-1 aplicado ao main no layout (footer fixo) | `grep -n "flex-1" "/home/joey/Documentos/Personal Projects/vyrko/resources/views/layouts/app.blade.php"` | Linha com `class="wrap page flex-1"` | DESIGN-02 |
| PLAN-02 / Task 2 | summary-grid removido do dashboard (3-card grid no lugar) | `grep -c "summary-grid" "/home/joey/Documentos/Personal Projects/vyrko/resources/views/dashboard/index.blade.php"` | `0` | DASH-01, DASH-02 |
| PLAN-02 / Task 3 | Landing h1 PT-BR atualizado | `grep -n "Seu currículo certo" "/home/joey/Documentos/Personal Projects/vyrko/resources/views/welcome.blade.php"` | Linha com a string encontrada | COPY-01 |
| PLAN-02 / Task 4 | Heading da página Templates e todos os empty states atualizados | `grep -rn "Qual currículo enviar para esta vaga" "/home/joey/Documentos/Personal Projects/vyrko/resources/views/resumes/templates.blade.php" && grep -rn "Nenhuma vaga analisada ainda" "/home/joey/Documentos/Personal Projects/vyrko/resources/views/dashboard/index.blade.php" && grep -rn "Nenhum currículo gerado ainda" "/home/joey/Documentos/Personal Projects/vyrko/resources/views/dashboard/index.blade.php" && grep -rn "Nenhum workspace ainda" "/home/joey/Documentos/Personal Projects/vyrko/resources/views/jobs/index.blade.php" && grep -rn "Nenhum currículo nesta vaga" "/home/joey/Documentos/Personal Projects/vyrko/resources/views/resumes/index.blade.php"` | Matches em todos os 5 greps | COPY-02 |
| PLAN-03 / Task 1 | loading-modal incluído no layout global | `grep -n "loading-modal\|x-loading-modal\|x-ui.loading-modal" "/home/joey/Documentos/Personal Projects/vyrko/resources/views/layouts/app.blade.php"` | Linha com `<x-loading-modal />` ou `<x-ui.loading-modal />` | DESIGN-03 |
| PLAN-03 / Task 2 | Datalist de área adicionado ao campo em profile-edit | `grep -n "area-suggestions" "/home/joey/Documentos/Personal Projects/vyrko/resources/views/career/profile-edit.blade.php"` | Linhas com `list="area-suggestions"` e `<datalist id="area-suggestions">` | COPY-03 |

---

## Phase-Level Build Check

Run after all tasks complete:

```bash
npm run build
```

Expected: exit code 0 — sem erros de compilação CSS/JS/Blade.

```bash
php artisan route:list
```

Expected: exit code 0 — sem erros de sintaxe PHP ou Blade.

---

## Full Phase Validation Script

```bash
#!/bin/bash
set -e
cd "/home/joey/Documentos/Personal Projects/vyrko"

echo "=== PLAN-01 Task 1: alpinejs installed ==="
node -e "const pkg = require('./package.json'); if (!pkg.dependencies?.alpinejs) { process.exit(1); } console.log('OK:', pkg.dependencies.alpinejs)"

echo "=== PLAN-01 Task 2: zero vscode refs ==="
for f in resources/css/app.css resources/views/career/index.blade.php resources/views/resumes/print.blade.php; do
  count=$(grep -c "vscode" "$f" 2>/dev/null || echo "0")
  if [ "$count" != "0" ]; then echo "FAIL: $f has $count vscode refs"; exit 1; fi
  echo "OK: $f"
done

echo "=== PLAN-02 Task 1: flex-1 in layout ==="
grep -q "flex-1" resources/views/layouts/app.blade.php && echo "OK" || (echo "FAIL"; exit 1)

echo "=== PLAN-02 Task 2: summary-grid removed from dashboard ==="
count=$(grep -c "summary-grid" resources/views/dashboard/index.blade.php 2>/dev/null || echo "0")
[ "$count" = "0" ] && echo "OK" || (echo "FAIL: $count occurrences found"; exit 1)

echo "=== PLAN-02 Task 3: landing h1 ==="
grep -q "Seu currículo certo" resources/views/welcome.blade.php && echo "OK" || (echo "FAIL"; exit 1)

echo "=== PLAN-02 Task 4: templates heading ==="
grep -q "Qual currículo enviar para esta vaga" resources/views/resumes/templates.blade.php && echo "OK" || (echo "FAIL"; exit 1)

echo "=== PLAN-02 Task 4: dashboard empty states ==="
grep -q "Nenhuma vaga analisada ainda" resources/views/dashboard/index.blade.php && echo "OK (vagas)" || (echo "FAIL"; exit 1)
grep -q "Nenhum currículo gerado ainda" resources/views/dashboard/index.blade.php && echo "OK (currículos)" || (echo "FAIL"; exit 1)

echo "=== PLAN-02 Task 4: jobs/resumes list empty states ==="
grep -q "Nenhum workspace ainda" resources/views/jobs/index.blade.php && echo "OK (jobs)" || (echo "FAIL"; exit 1)
grep -q "Nenhum currículo nesta vaga" resources/views/resumes/index.blade.php && echo "OK (resumes)" || (echo "FAIL"; exit 1)

echo "=== PLAN-03 Task 1: loading-modal in layout ==="
grep -qE "x-loading-modal|x-ui\.loading-modal" resources/views/layouts/app.blade.php && echo "OK" || (echo "FAIL"; exit 1)

echo "=== PLAN-03 Task 2: datalist in profile-edit ==="
grep -q "area-suggestions" resources/views/career/profile-edit.blade.php && echo "OK" || (echo "FAIL"; exit 1)

echo "=== Build check ==="
npm run build && echo "OK: build clean" || (echo "FAIL: build errors"; exit 1)

echo ""
echo "ALL VALIDATIONS PASSED"
```
