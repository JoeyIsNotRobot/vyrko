# Phase 2: Inventário com Import — Research

**Researched:** 2026-06-21
**Domain:** File upload (PDF/DOCX/TXT) + extração de texto PHP + integração com AI service existente + Alpine.js modal de progresso
**Confidence:** HIGH

---

## Summary

O backend de importação já existe e é robusto: `ResumeImportController` → `ResumeImportService` → `AiClient::completeJson('resume_import_parse', ...)`. O serviço já aceita `.txt` e `.docx` (via ZipArchive nativo). Para PDF, o código atual usa o binário `pdftotext` via `Symfony\Component\Process` — e esse binário está disponível no ambiente local (`pdftotext 24.02.0`). O form de importação atual está enterrado na sidebar direita (`inventory-list.blade.php`, `.career-quality-column`) e precisa ser movido para o topo da página como card full-width.

O prompt de IA `resumeImportParsePrompt` já define um schema detalhado com todos os campos exigidos pelo INV-03 (nome, cargo, empresa/cargo/período/responsabilidades, skills, educação, idiomas com nível, certificações). O problema principal é de UX, não de lógica de backend: a seção de importação precisa de destaque, drop zone com drag-and-drop, e o modal de loading (`<x-ui.loading-modal />`) precisa ser integrado ao submit AJAX já existente.

O `data-career-ajax` submit handler em `app.js` já faz fetch assíncrono e atualiza `#career-items` com HTML do servidor — mas não integra com o `loadingModal`. A fase 2 precisa: (1) mover/redesenhar o card de importação, (2) adicionar drag-and-drop na drop zone, (3) conectar o submit do form de import ao `loadingModal` com steps simulados via `setTimeout`, e (4) exibir badge "via IA" nos itens recém-importados.

**Recomendação primária:** Não instalar nenhuma biblioteca PHP nova para parsing. `pdftotext` já funciona no ambiente. DOCX via `ZipArchive` nativo já funciona. O trabalho é 90% frontend (card, drop zone, modal integration) e 10% backend (badge "via IA" via flash session).

---

<phase_requirements>
## Phase Requirements

| ID | Descrição | Suporte de Research |
|----|-----------|---------------------|
| INV-01 | Seção de importação exibida no topo da página com destaque, aceitando PDF, DOCX e TXT explicitamente | Card full-width acima de `.career-summary-grid`; form atual na sidebar precisa ser removido/substituído |
| INV-02 | Importação exibe modal de loading com barra de progresso por etapa: "Lendo documento → Extraindo dados → Organizando perfil → Concluído" | `<x-ui.loading-modal />` já existe; `loadingModal.open()`, `.advance()`, `.succeed()`, `.fail()` já implementados em `app.js`; precisa conectar ao submit do form de import |
| INV-03 | Prompt de IA mapeia campo a campo sem inferências genéricas | Prompt `resumeImportParsePrompt` em `GeminiAiClient` já tem schema completo; precisa revisar/fortalecer a instrução anti-inferência e verificar campos ausentes do schema atual |
</phase_requirements>

---

## Architectural Responsibility Map

| Capability | Primary Tier | Secondary Tier | Rationale |
|------------|-------------|----------------|-----------|
| Upload de arquivo (validação tipo/tamanho) | API / Backend | — | `ResumeImportRequest` com `mimes` e `max` — já implementado |
| Extração de texto de PDF | API / Backend | — | `pdftotext` via `Symfony\Process` — já implementado |
| Extração de texto de DOCX | API / Backend | — | `ZipArchive` nativo PHP — já implementado |
| Parsing IA campo a campo | API / Backend | — | `GeminiAiClient::resumeImportParsePrompt` — já implementado, precisa revisão |
| Persistência dos dados extraídos | Database / Storage | API / Backend | `ResumeImportService::persist*` — já implementado com `updateOrCreate` |
| Drop zone drag-and-drop | Browser / Client | — | Alpine.js + eventos nativos `dragover`/`drop` |
| Feedback visual de progresso (modal steps) | Browser / Client | — | `loadingModal` Alpine.data já registrado; precisa conectar ao submit |
| Badge "via IA" nos itens | Browser / Client | Frontend Server | localStorage com TTL 30min OU flash session Laravel |
| Banner de sucesso pós-importação | Browser / Client | — | Alpine `x-show` com `x-transition` após resposta JSON bem-sucedida |

---

## Codebase Discovery — Estado Atual

### Fluxo existente de importação

```
POST /career/import
  → ResumeImportController::__invoke()
      → ResumeImportService::importUploadedFile($user, $file)
          → extractText($file)          # txt: file_get_contents | docx: ZipArchive | pdf: pdftotext binary
          → importText($user, $text)
              → normalizeText($text)
              → AiClient::completeJson('resume_import_parse', ['resume_text' => $normalized])
              → DB::transaction: persistProfile, persistSkills, persistExperiences, persistProjects,
                                 persistEducations, persistCertifications, persistLanguages, persistAchievements
      → careerResponse($request, message)  # JSON: {message, html, experienceOptions}
```

**Rota:** `POST /career/import` → `career.import` (auth + verified middleware)

**FormRequest:** `ResumeImportRequest` — regras atuais: `required|file|mimes:pdf,txt,docx|max:5120` (5MB)

**Resposta JSON:** `{message, html, experienceOptions}` via trait `RespondsWithCareerState`
- `html` = `career.partials.inventory-list` renderizado com o user fresco
- Frontend atual (`app.js`) substitui `#career-items` com esse HTML

### Form atual (a ser substituído)

Localização: `resources/views/career/partials/inventory-list.blade.php`, linha 242–257, dentro de `.career-quality-column`.

```html
<form class="stack" method="POST" action="{{ route('career.import') }}" enctype="multipart/form-data" data-career-ajax>
    @csrf
    <div>
        <label>{{ __('messages.fields.file') }}</label>
        <input name="resume" type="file" accept=".pdf,.docx,.txt,...">
    </div>
    <button class="btn secondary" type="submit" data-loading-text="Importando...">{{ __('messages.actions.import') }}</button>
</form>
```

O atributo `data-career-ajax` faz o handler de submit em `app.js` processar esse form via fetch. **Esse mesmo atributo deve ser preservado no novo card** — a lógica AJAX já funciona.

### Handler AJAX existente em app.js (linhas 126–186)

O handler `data-career-ajax` já:
- Faz `fetch(form.action, { method: 'POST', body: new FormData(form) })`
- Recebe `{ message, html }` e substitui `#career-items`
- Exibe erros via `showErrors()`
- **NÃO** integra com `loadingModal`

**O que falta:** Antes do `fetch`, disparar `open-loading-modal` com os 4 steps. Durante a espera, avançar steps com `setTimeout`. Ao receber resposta: chamar `loadingModal.succeed()` ou `loadingModal.fail(message)`.

### API do loadingModal (app.js linhas 4–28)

```javascript
Alpine.data('loadingModal', () => ({
    open(steps)          // abre o modal, reseta progresso
    advance(stepIndex)   // avança para step[stepIndex], calcula % automaticamente
    succeed()            // progress=100, fecha após 400ms
    fail(message)        // exibe error state com mensagem
}));
```

**Invocação via evento (conforme UI-SPEC):**
```javascript
window.dispatchEvent(new CustomEvent('open-loading-modal', {
    detail: { steps: ['Lendo documento', 'Extraindo dados', 'Organizando perfil', 'Concluído'] }
}));
```

Verificar se o componente `loading-modal.blade.php` já escuta `open-loading-modal` — **ele não escuta**. O componente usa `x-data="loadingModal()"` mas não tem listener para o CustomEvent. O executor precisará adicionar `@open-loading-modal.window="open($event.detail.steps)"` ao componente, ou chamar a instância Alpine diretamente.

---

## Standard Stack

### Core (sem mudanças — zero novas dependências)

| Componente | Versão | Propósito | Status |
|------------|--------|-----------|--------|
| `pdftotext` (poppler-utils) | 24.02.0 | Extração de texto de PDF | Disponível no ambiente local [VERIFIED: bash check] |
| `ZipArchive` (PHP ext-zip) | nativo PHP 8.3 | Extração de texto de DOCX | Disponível [VERIFIED: bash check] |
| `Symfony\Component\Process` | já no Laravel | Executar `pdftotext` como subprocess | Já usado no `ResumeImportService` |
| Alpine.js v3 | já instalado | Drop zone drag-and-drop + modal integration | Já em `app.js` |
| `GeminiAiClient` | existente | IA para parsing campo a campo | Já implementado |

### Por que não instalar `smalot/pdfparser` ou `phpoffice/phpword`

**smalot/pdfparser** (v2.12.5, 39M downloads, última release abr/2026 [VERIFIED: packagist.org]):
- Extrai texto de PDF em pure PHP, sem binário externo
- Desvantagem: não suporta bem PDFs com layout complexo (multi-coluna, tabelas, texto rotacionado); `pdftotext` com flag `-layout` é superior para currículos
- Desvantagem adicional: adiciona dependência Composer em uma base que hoje tem zero dependências de terceiros além de Laravel

**spatie/pdf-to-text** (v1.55.0, 7.5M downloads, última release nov/2025 [VERIFIED: packagist.org]):
- É um wrapper para `pdftotext` — exatamente o que o `ResumeImportService` já faz nativamente via `Symfony\Process`
- Instalar seria adicionar uma dependência para fazer o mesmo que o código atual já faz

**phpoffice/phpword** (v1.4.0, 38M downloads, última release jun/2025 [VERIFIED: packagist.org]):
- Suporta leitura de DOCX, DOC, RTF, ODF, HTML
- Desvantagem: a extração de texto atual via `ZipArchive` + `strip_tags` já funciona para o caso de uso (extrair plain text de `word/document.xml`)
- A vantagem real do PHPWord seria para _escrever_ DOCX, não ler. Para leitura de plain text, a implementação atual é adequada.

**Decisão:** Manter a implementação atual. Zero novas dependências Composer. [ASSUMED: decisão adequada para o escopo; se o usuário reportar falha em PDFs específicos, reavaliar `smalot/pdfparser`]

---

## Package Legitimacy Audit

> Nenhum pacote novo sendo instalado nesta fase.

| Package | Decisão |
|---------|---------|
| smalot/pdfparser | DESCARTADO — `pdftotext` nativo é superior para o caso de uso |
| spatie/pdf-to-text | DESCARTADO — wrapper do que já existe |
| phpoffice/phpword | DESCARTADO — ZipArchive nativo cobre a leitura de texto |

**Pacotes removidos por slopcheck:** nenhum (slopcheck não foi executado; nenhum pacote sendo instalado)
**Pacotes suspeitos:** nenhum

---

## Architecture Patterns

### Diagrama de Fluxo — Import com Modal

```
[Usuário: career/index]
        |
        v
[Card de importação (topo)]
        |
   Seleciona arquivo
   (click ou drag-drop)
        |
        v
[Drop zone: exibe nome do arquivo]
        |
   Clica "Importar currículo"
        |
        v
[app.js: submit interceptado]
        |
        +---> dispatchEvent('open-loading-modal', {steps:[...]})
        |              |
        |              v
        |     [loadingModal: abre, step 0 "Lendo documento" 0%]
        |
        +---> fetch POST /career/import (FormData)
        |              |
        |     setTimeout 800ms -> advance(1) "Extraindo dados" 50%
        |     setTimeout 1600ms -> advance(2) "Organizando perfil" 75%
        |              |
        |              v (resposta do servidor)
        |     
        +---> OK: advance(3) "Concluído" → succeed() → [modal fecha 400ms]
        |          → substitui #career-items com HTML novo
        |          → exibe banner de sucesso
        |          → aplica badge "via IA" nos itens (localStorage)
        |
        +---> ERRO: fail("mensagem de erro")
                   → [modal mostra error state]
                   → botão "Tentar novamente" fecha modal
```

### Estrutura de arquivos afetados

```
resources/
├── views/
│   ├── career/
│   │   ├── index.blade.php                 # Modificar — adicionar CSS do card de importação
│   │   └── partials/
│   │       └── inventory-list.blade.php    # Modificar — mover card para topo, remover da sidebar
│   └── components/ui/
│       └── loading-modal.blade.php         # Modificar — adicionar listener @open-loading-modal.window
└── js/
    └── app.js                              # Modificar — conectar import submit ao loadingModal + drag-drop
```

### Padrão 1: Conectar submit AJAX ao loadingModal

O handler `data-career-ajax` em `app.js` precisa detectar se o form sendo submetido é o de import (verificar `form.action` ou um atributo `data-import-form`). Ao detectar, disparar o modal antes do fetch e avançar os steps com timeouts enquanto aguarda.

```javascript
// Em app.js, dentro do handler 'submit' data-career-ajax — trecho a adicionar:
if (form.dataset.importForm !== undefined) {
    const modal = document.querySelector('[x-data]')?._x_dataStack?.[0]; // forma frágil
    // Preferir: dispatchEvent e deixar o componente Alpine responder
    window.dispatchEvent(new CustomEvent('open-loading-modal', {
        detail: { steps: ['Lendo documento', 'Extraindo dados', 'Organizando perfil', 'Concluído'] }
    }));
    // steps simulados enquanto aguarda resposta:
    const t1 = setTimeout(() => window.dispatchEvent(new CustomEvent('advance-loading-modal', { detail: { step: 1 } })), 800);
    const t2 = setTimeout(() => window.dispatchEvent(new CustomEvent('advance-loading-modal', { detail: { step: 2 } })), 1800);
    // ao receber resposta: succeed() ou fail()
}
```

**Alternativa mais limpa:** Adicionar `@open-loading-modal.window` e `@advance-loading-modal.window` ao componente `loading-modal.blade.php`.

### Padrão 2: Drag-and-drop com Alpine.js

```javascript
// Inline no card de importação (x-data no card pai)
{
    file: null,
    dragOver: false,
    handleDrop(event) {
        event.preventDefault();
        this.dragOver = false;
        const f = event.dataTransfer?.files?.[0];
        if (f) this.selectFile(f);
    },
    selectFile(f) {
        const allowed = ['.pdf', '.docx', '.txt'];
        const ext = '.' + f.name.split('.').pop().toLowerCase();
        if (!allowed.includes(ext)) {
            // exibir erro inline
            return;
        }
        this.file = f;
        // Injetar no input[type=file] via DataTransfer
        const dt = new DataTransfer();
        dt.items.add(f);
        document.querySelector('input[name="resume"]').files = dt.files;
    }
}
```

### Padrão 3: Badge "via IA" — localStorage com TTL

Após import bem-sucedido, o servidor retorna HTML novo para `#career-items`. O JavaScript precisa marcar os itens recém-criados. O approach mais simples: o JSON de resposta do `careerResponse` pode incluir IDs dos itens criados nessa importação. O frontend então armazena esses IDs no localStorage com TTL de 30min e aplica o badge ao renderizar.

**Alternativa mais simples (recomendada):** Flash session do Laravel. `ResumeImportService` pode retornar os IDs criados, e o controller adicionar um flash `session(['import_source_ids' => $ids])`. O Blade view então renderiza o badge baseado na session.

### Anti-Patterns a Evitar

- **Não duplicar o handler AJAX:** O `data-career-ajax` em `app.js` já funciona para todos os forms de career. Não criar um handler separado para import — apenas adicionar detecção de `data-import-form` dentro do handler existente.
- **Não recarregar a página:** O controller já suporta JSON response. O form deve continuar com `data-career-ajax`.
- **Não usar SSE:** Conforme decisão já tomada (UI-SPEC, Additional Context). Progresso simulado via `setTimeout`.
- **Não adicionar validação de MIME no frontend como substituta:** A validação de `mimes:pdf,txt,docx` no `ResumeImportRequest` é a fonte de verdade. Frontend apenas previne UX ruim ao selecionar.

---

## Don't Hand-Roll

| Problema | Não construir | Usar em vez disso | Por quê |
|----------|--------------|-------------------|----|
| Extração de texto PDF | Parser PHP custom | `pdftotext` (já usa) | Suporta PDFs complexos com `-layout`; 40+ anos de desenvolvimento do Poppler |
| Extração de texto DOCX | Parser XML custom | `ZipArchive` + `strip_tags` (já usa) | DOCX é um ZIP de XMLs — a abordagem atual já funciona corretamente |
| Progress bar do modal | CSS animation custom | `loadingModal.advance(stepIndex)` (já existe) | Componente já calcula percentual automaticamente |
| Drag-and-drop de arquivo | Biblioteca JS | Alpine.js + eventos nativos `dragover`/`drop` | Stack já usa Alpine; eventos nativos cobrem 100% do caso de uso |
| Substituição de `input.files` via drag | FileReader | `DataTransfer` API | API padrão para injetar File em input[type=file] |

---

## Common Pitfalls

### Pitfall 1: `open-loading-modal` CustomEvent não escutado pelo componente Alpine

**O que dá errado:** `window.dispatchEvent(new CustomEvent('open-loading-modal', ...))` é disparado mas o modal não abre, porque o `loading-modal.blade.php` atual não tem `@open-loading-modal.window`.

**Por que acontece:** O componente foi criado na Phase 1 com a spec de ser invocado via evento, mas o listener não foi implementado — o componente só expõe os métodos Alpine (`open`, `advance`, `succeed`, `fail`).

**Como evitar:** Adicionar ao div raiz do `loading-modal.blade.php`:
```html
@open-loading-modal.window="open($event.detail.steps)"
@advance-loading-modal.window="advance($event.detail.step)"
@succeed-loading-modal.window="succeed()"
@fail-loading-modal.window="fail($event.detail.message)"
```

**Sinal de alerta:** Modal não abre ao submeter o form de import.

### Pitfall 2: `DataTransfer` API para injetar arquivo no input após drag-drop

**O que dá errado:** Drag-drop captura o `File`, mas ao submeter o form, o `input[type=file]` está vazio porque o arquivo foi atribuído à variável Alpine mas não ao `files` do input nativo.

**Por que acontece:** `input.files` é somente-leitura exceto quando atribuído via `DataTransfer`.

**Como evitar:**
```javascript
const dt = new DataTransfer();
dt.items.add(droppedFile);
document.querySelector('input[name="resume"]').files = dt.files;
```

**Sinal de alerta:** Validação do backend retorna "campo resume é obrigatório" mesmo após drag-drop.

### Pitfall 3: Form `enctype="multipart/form-data"` ausente no card novo

**O que dá errado:** `new FormData(form)` captura o arquivo, mas o servidor recebe um payload sem o arquivo porque o form não tem `enctype`.

**Como evitar:** O form do card de importação deve ter `enctype="multipart/form-data"` — igual ao form atual em `inventory-list.blade.php`.

### Pitfall 4: ESC bloqueado mesmo no estado de erro

**O que dá errado:** Usuário fica preso no modal após erro de import.

**Spec correta (UI-SPEC):** ESC deve ser liberado quando `error !== null`.

**Implementação atual no `loading-modal.blade.php`:**
```html
@keydown.escape.window.prevent="if (show && !error) $event.preventDefault()"
```
Isso já está correto — verificar se o `if (!error)` está presente. Está implementado como `@keydown.escape.window.prevent` sem condicional. **Precisará ser corrigido** para `@keydown.escape.window="if (show && !error) $event.preventDefault()"`.

### Pitfall 5: `pdftotext` retorna texto vazio para PDFs escaneados (imagem)

**O que dá errado:** PDF é um scan (sem camada de texto). `pdftotext` retorna string vazia. `normalizeText` retorna `''`. `importText` lança `RuntimeException('O currículo enviado não possui texto suficiente...')`.

**Como evitar:** Esse erro já é tratado pelo service. O controller já captura `RuntimeException` e retorna `422`. O frontend precisa exibir a mensagem de erro no modal via `fail()`. A mensagem já está definida no UI-SPEC.

---

## Prompt de IA — Análise do `resumeImportParsePrompt` atual

### O que já está correto

O prompt atual em `GeminiAiClient::resumeImportParsePrompt()` define:
- Schema completo: `profile`, `skills`, `experiences`, `projects`, `educations`, `certifications`, `languages`, `achievements`
- Instrução anti-inferência: "Não complete lacunas por inferência; se o texto não disser, use null ou array vazio"
- Regra de datas: `YYYY-MM-DD` quando houver mês/ano suficiente
- Categories de skill: backend/frontend/database/devops/cloud/testing/soft_skill/language/tool/other
- Campos de idioma com `proficiency` (nivel) — atende INV-03

### O que pode ser melhorado para INV-03

O schema atual usa `'projects': ['array']`, `'educations': ['array']`, etc. como tipos vagos — o Gemini pode não saber o sub-schema esperado. Comparando com o schema de `experiences` (totalmente tipado), os demais estão subdesenvolvidos.

**Campos ausentes / vagos no schema atual:**
- `educations`: sub-schema não especificado (deveria ser: `institution`, `degree`, `field_of_study`, `start_date`, `end_date`)
- `certifications`: sub-schema não especificado (deveria ser: `name`, `issuer`, `issued_at`, `expires_at`, `credential_url`)
- `languages`: sub-schema não especificado (deveria ser: `language`, `proficiency`, `notes`)
- `projects`: sub-schema não especificado

O `persistCertifications`, `persistEducations`, `persistLanguages` e `persistProjects` já esperam esses campos — mas o prompt não os documenta explicitamente. Atualizar o schema no prompt é parte do trabalho de INV-03.

---

## Code Examples

### Invocação do modal pelo submit de import (padrão a seguir)

```javascript
// Em app.js — dentro do handler submit data-career-ajax
// Detectar se é o form de import
if (form.dataset.importForm !== undefined) {
    window.dispatchEvent(new CustomEvent('open-loading-modal', {
        detail: { steps: ['Lendo documento', 'Extraindo dados', 'Organizando perfil', 'Concluído'] }
    }));
    const t1 = setTimeout(() => window.dispatchEvent(
        new CustomEvent('advance-loading-modal', { detail: { step: 1 } })
    ), 800);
    const t2 = setTimeout(() => window.dispatchEvent(
        new CustomEvent('advance-loading-modal', { detail: { step: 2 } })
    ), 1800);
    // Limpar timeouts ao receber resposta
}
// Ao sucesso:
window.dispatchEvent(new CustomEvent('succeed-loading-modal'));
// Ao erro:
window.dispatchEvent(new CustomEvent('fail-loading-modal', { detail: { message: errMsg } }));
```

### Drop zone Alpine (estrutura mínima)

```html
<div x-data="{
    file: null,
    dragOver: false,
    error: null,
    handleDrop(e) {
        e.preventDefault();
        this.dragOver = false;
        const f = e.dataTransfer?.files?.[0];
        if (f) this.setFile(f);
    },
    setFile(f) {
        const allowed = ['pdf', 'docx', 'txt'];
        const ext = f.name.split('.').pop().toLowerCase();
        if (!allowed.includes(ext)) {
            this.error = 'Formato não suportado. Use PDF, DOCX ou TXT.';
            return;
        }
        this.error = null;
        this.file = f;
        const dt = new DataTransfer();
        dt.items.add(f);
        $refs.fileInput.files = dt.files;
    }
}"
    @dragover.prevent="dragOver = true"
    @dragleave="dragOver = false"
    @drop="handleDrop($event)">

    <label :class="dragOver ? 'drag-active' : ''" @click="$refs.fileInput.click()">
        <input x-ref="fileInput" type="file" name="resume" accept=".pdf,.docx,.txt" class="sr-only"
               @change="setFile($event.target.files[0])">
        <!-- conteúdo visual da drop zone -->
    </label>
</div>
```

### listener no loading-modal.blade.php

```html
<div
    x-data="loadingModal()"
    @open-loading-modal.window="open($event.detail.steps)"
    @advance-loading-modal.window="advance($event.detail.step)"
    @succeed-loading-modal.window="succeed()"
    @fail-loading-modal.window="fail($event.detail.message)"
    ...
>
```

---

## State of the Art

| Abordagem anterior | Abordagem atual | Impacto |
|-------------------|-----------------|---------|
| Form de importação na sidebar direita (escondido) | Card full-width no topo com destaque | INV-01: visibilidade |
| Input `<file>` simples | Drop zone com drag-and-drop | UX conforme UI-SPEC |
| Submit síncrono com redirect | Fetch AJAX + modal de progresso | INV-02: feedback visual |
| Prompt com schemas vagos em `projects/educations/certifications` | Schema tipado campo a campo para todos os objetos | INV-03: qualidade da extração |

---

## Assumptions Log

| # | Claim | Seção | Risco se errado |
|---|-------|-------|----------------|
| A1 | `pdftotext` disponível em produção (Docker/Sail) | Standard Stack | PDF import quebraria em produção; precisaria instalar `poppler-utils` no Dockerfile |
| A2 | `DataTransfer` API disponível nos browsers-alvo dos usuários | Code Examples | Drag-drop não funciona; fallback: input file click-only ainda funciona |
| A3 | O executor pode inlinar o card em `inventory-list.blade.php` em vez de criar `import-card.blade.php` | Architecture Patterns | Sem risco técnico; apenas questão de organização |

---

## Open Questions (RESOLVED)

1. **`pdftotext` em produção (Docker)** — RESOLVED: out-of-scope para esta fase. Deployar com `poppler-utils` no Dockerfile é concern de infra, não bloqueia execução local. Assumption A1 documentada.

2. **Comportamento do modal ao receber resposta antes do step 2** — RESOLVED: Plan 02 Task 2 implementa `clearTimeout(t1); clearTimeout(t2)` antes de chamar `succeed()`, garantindo que resposta rápida do servidor não deixa timers pendentes.

3. **Badge "via IA" — session flash vs. localStorage** — RESOLVED: Plan 03 Task 2 usa `session()->flash('just_imported', true)` (flash boolean). Mais simples, server-side, sem mudanças no ResumeImportService.

---

## Environment Availability

| Dependência | Requerida por | Disponível | Versão | Fallback |
|-------------|--------------|------------|--------|----------|
| `pdftotext` (poppler-utils) | Extração de texto PDF | ✓ (local) | 24.02.0 | smalot/pdfparser (se instalado) |
| `ZipArchive` (PHP ext-zip) | Extração de texto DOCX | ✓ | PHP 8.3 nativo | Nenhum |
| Alpine.js v3 | Drop zone + modal | ✓ | já em `app.js` | — |
| Gemini 2.5 Flash | Parsing IA | ✓ | via `AI_API_KEY` | `FakeAiClient` em testes |

**Dependências ausentes sem fallback:** nenhuma.

**A1 (pdftotext em Docker/produção):** Verificar — pode ser dependência faltante no container de produção.

---

## Validation Architecture

### Test Framework

| Propriedade | Valor |
|-------------|-------|
| Framework | PHPUnit 12.5 |
| Config file | `phpunit.xml` (padrão Laravel) |
| Quick run | `php artisan test --filter=import` |
| Full suite | `php artisan test` |

### Phase Requirements → Test Map

| Req ID | Comportamento | Tipo | Comando | Arquivo existe? |
|--------|--------------|------|---------|-----------------|
| INV-01 | Card de importação renderiza no topo de `career/index` | Feature | `php artisan test --filter=test_card_importacao_aparece_no_topo` | ❌ Wave 0 |
| INV-02 | Submit retorna JSON `{message, html}` para form de import (modal: frontend only) | Feature | `php artisan test --filter=test_importacao_de_curriculo_txt_absorve_informacoes` | ✅ já existe (`VyrkoMvpTest`) |
| INV-03 | Campos extraídos pelo prompt: nome, empresa, idioma com nível, certificações | Feature | `php artisan test --filter=test_importacao_mapeia_campos_campo_a_campo` | ❌ Wave 0 |

### Wave 0 Gaps

- [ ] `tests/Feature/VyrkoMvpTest.php` — adicionar `test_card_importacao_aparece_no_topo`: verifica que `career/index` renderiza o card acima da `.career-summary-grid` com o form `action="{{ route('career.import') }}"` e `enctype="multipart/form-data"`
- [ ] `tests/Feature/VyrkoMvpTest.php` — adicionar `test_importacao_mapeia_campos_campo_a_campo`: verifica que após import, `candidate_languages` tem registro com campo `proficiency` preenchido e `candidate_certifications` tem `name`

---

## Security Domain

### ASVS Categories Aplicáveis

| Categoria ASVS | Aplica | Controle |
|----------------|--------|---------|
| V5 Input Validation | sim | `ResumeImportRequest`: `mimes:pdf,txt,docx`, `max:5120` — já implementado |
| V12 File Upload | sim | Ver controles abaixo |
| V2 Authentication | não | Rota já protegida por `auth` + `verified` middleware |

### Controles de Upload (V12)

| Controle | Status | Implementação |
|----------|--------|---------------|
| Validação de MIME type | ✓ Implementado | `mimes:pdf,txt,docx` no `ResumeImportRequest` |
| Tamanho máximo | ✓ Implementado | `max:5120` (5MB) — adequado para currículos |
| Arquivo salvo em temp do PHP (não exposto publicamente) | ✓ | `UploadedFile::getRealPath()` aponta para `/tmp` — nunca salvo em `storage/public` |
| Conteúdo do arquivo não executado | ✓ | Texto extraído e enviado como string para a IA; sem `eval`, sem `include` |
| Path traversal | ✓ | `getRealPath()` é controlado pelo PHP; sem interpolação de nome de arquivo em paths |

**Não aplicável:** O arquivo não é persistido após extração do texto. O texto extraído é enviado à IA e descartado.

---

## Sources

### Primary (HIGH confidence)
- Codebase: `app/Services/Import/ResumeImportService.php` — fluxo completo de extração e persistência
- Codebase: `app/Services/Ai/GeminiAiClient.php` — prompt `resumeImportParsePrompt` atual
- Codebase: `resources/js/app.js` — handler `data-career-ajax` e API do `loadingModal`
- Codebase: `resources/views/components/ui/loading-modal.blade.php` — componente Alpine atual
- Codebase: `app/Http/Requests/Import/ResumeImportRequest.php` — validação atual
- Bash: `command -v pdftotext && pdftotext -v` — confirma disponibilidade do binário
- Bash: `php -r "echo class_exists('ZipArchive')"` — confirma ZipArchive disponível

### Secondary (MEDIUM confidence)
- packagist.org/packages/smalot/pdfparser — v2.12.5, 39M downloads, abr/2026
- packagist.org/packages/spatie/pdf-to-text — v1.55.0, 7.5M downloads, nov/2025
- packagist.org/packages/phpoffice/phpword — v1.4.0, 38M downloads, jun/2025

---

## Metadata

**Confidence breakdown:**
- Fluxo de backend existente: HIGH — código lido diretamente
- API do loadingModal: HIGH — código lido diretamente
- Decisão de não instalar novas dependências: HIGH — binários verificados disponíveis
- Prompt de IA: HIGH — código lido; gaps identificados por análise
- Integração modal + form AJAX: MEDIUM — padrão de eventos Alpine verificado em docs implícitos; listener não existe ainda e precisará ser adicionado

**Research date:** 2026-06-21
**Valid until:** 2026-08-21 (stack estável; expira se Laravel ou Alpine tiver breaking changes)
