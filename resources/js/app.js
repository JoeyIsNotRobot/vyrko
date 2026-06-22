import Alpine from 'alpinejs';
window.Alpine = Alpine;

Alpine.data('loadingModal', () => ({
    show: false,
    progress: 0,
    currentStep: '',
    steps: [],
    error: null,
    open(steps) {
        this.steps = steps;
        this.currentStep = steps[0] ?? '';
        this.progress = 0;
        this.error = null;
        this.show = true;
    },
    advance(stepIndex) {
        this.currentStep = this.steps[stepIndex] ?? '';
        this.progress = Math.round(((stepIndex + 1) / this.steps.length) * 100);
    },
    succeed() {
        this.progress = 100;
        setTimeout(() => { this.show = false; }, 400);
    },
    fail(message) {
        this.error = message;
    }
}));

Alpine.data('linkedinFetch', () => ({
    url: '',
    status: 'idle', // idle | loading | success | error
    errorMsg: '',

    extractJobId(url) {
        try {
            const u = new URL(url);
            if (! u.hostname.includes('linkedin.com')) return null;
            const fromParam = u.searchParams.get('currentJobId');
            if (fromParam && /^\d+$/.test(fromParam)) return fromParam;
            const match = u.pathname.match(/\/jobs\/view\/(\d+)/);
            return match ? match[1] : null;
        } catch {
            return null;
        }
    },

    async fetchJob(jobId) {
        this.status = 'loading';
        try {
            const res = await fetch('/jobs/fetch-linkedin', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ job_id: jobId }),
            });
            if (! res.ok) throw new Error();
            const data = await res.json();
            if (data.title) document.querySelector('[name="title"]').value = data.title;
            if (data.company) document.querySelector('[name="company_name"]').value = data.company;
            if (data.description) document.querySelector('[name="job_description"]').value = data.description;
            this.status = 'success';
        } catch {
            this.status = 'error';
            this.errorMsg = 'Não foi possível buscar a vaga. Preencha manualmente.';
        }
    },

    onInput(val) {
        const jobId = this.extractJobId(val);
        if (jobId) this.fetchJob(jobId);
    },

    onPaste(event) {
        const text = (event.clipboardData || window.clipboardData).getData('text');
        this.url = text;
        const jobId = this.extractJobId(text);
        if (jobId) this.fetchJob(jobId);
    },
}));

Alpine.start();

const config = window.Vyrko ?? {};

const menuToggle = document.querySelector('[data-menu-toggle]');
const menu = document.querySelector('[data-menu]');

menuToggle?.addEventListener('click', () => {
    const isOpen = menu?.classList.toggle('is-open');
    menuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
});

document.addEventListener('change', (event) => {
    const select = event.target.closest('[data-locale-select]');
    if (!select || !select.value) return;

    window.location.href = select.value;
});

document.addEventListener('click', (event) => {
    const link = event.target.closest('[data-loading-link]');
    if (!link) return;

    link.classList.add('is-loading');
    link.setAttribute('aria-disabled', 'true');

    const label = link.querySelector('[data-label]');
    if (label && link.dataset.loadingText) {
        label.textContent = link.dataset.loadingText;
    } else if (link.dataset.loadingText) {
        link.textContent = link.dataset.loadingText;
    }
});

document.addEventListener('submit', (event) => {
    const form = event.target.closest('form[data-loading]');
    if (!form) return;

    form.classList.add('is-loading');
    form.querySelectorAll('button[type="submit"]').forEach((button) => {
        button.dataset.originalText = button.textContent;
        button.textContent = button.dataset.loadingText || config.processingText || 'Processando...';
    });
});

const statusBox = () => document.getElementById('career-status');
const errorBox = () => document.getElementById('career-errors');
const token = document.querySelector('meta[name="csrf-token"]')?.content;

const showMessage = (message) => {
    const box = statusBox();
    if (!box) return;

    box.textContent = message;
    box.hidden = false;
};

const showErrors = (errors) => {
    const box = errorBox();
    if (!box) return;

    const list = Array.isArray(errors) ? errors : Object.values(errors || {}).flat();

    box.replaceChildren();
    const heading = document.createElement('strong');
    heading.textContent = config.reviewDataText || 'Revise os dados:';
    const ul = document.createElement('ul');
    list.forEach((item) => {
        const li = document.createElement('li');
        li.textContent = item;
        ul.appendChild(li);
    });
    box.appendChild(heading);
    box.appendChild(ul);
    box.hidden = false;
    box.scrollIntoView({behavior: 'smooth', block: 'center'});
};

document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-toggle-edit]');
    if (!button) return;

    const item = button.closest('.inventory-item');
    const panel = item?.querySelector('.inline-edit');
    const template = item?.querySelector('template[data-edit-template]');

    if (!panel) return;

    if (!panel.dataset.loaded && template) {
        panel.append(template.content.cloneNode(true));
        panel.dataset.loaded = 'true';
    }

    panel.hidden = !panel.hidden;
});

document.addEventListener('submit', async (event) => {
    const form = event.target.closest('form[data-career-ajax]');
    if (!form) return;

    event.preventDefault();

    statusBox()?.setAttribute('hidden', 'hidden');
    errorBox()?.setAttribute('hidden', 'hidden');
    form.classList.add('is-loading');

    form.querySelectorAll('button[type="submit"]').forEach((button) => {
        button.dataset.originalText = button.textContent;
        if (button.dataset.loadingText) button.textContent = button.dataset.loadingText;
    });

    let importTimers = [];
    const isImport = form.dataset.importForm !== undefined; // triggers when form has data-import-form attribute

    if (isImport) {
        window.dispatchEvent(new CustomEvent('open-loading-modal', {
            detail: { steps: ['Lendo documento', 'Extraindo dados', 'Organizando perfil', 'Concluído'] }
        }));
        importTimers.push(setTimeout(() => {
            window.dispatchEvent(new CustomEvent('advance-loading-modal', { detail: { step: 1 } }));
        }, 800));
        importTimers.push(setTimeout(() => {
            window.dispatchEvent(new CustomEvent('advance-loading-modal', { detail: { step: 2 } }));
        }, 1800));
    }

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...(token ? {'X-CSRF-TOKEN': token} : {}),
            },
            body: new FormData(form),
        });

        if (isImport) importTimers.forEach(clearTimeout);

        const text = await response.text();
        let payload;
        try {
            payload = JSON.parse(text);
        } catch {
            payload = { message: response.status === 419
                ? 'Sessão expirada. Recarregue a página.'
                : `Erro do servidor (${response.status}).` };
        }

        if (!response.ok) {
            if (isImport) {
                const errMsg = payload.message || (payload.errors ? Object.values(payload.errors).flat()[0] : null) || 'Algo deu errado ao processar o arquivo. Tente novamente em alguns instantes.';
                window.dispatchEvent(new CustomEvent('fail-loading-modal', { detail: { message: errMsg } }));
                return;
            }
            showErrors(payload.errors || [payload.message || 'Erro']);
            return;
        }

        const items = document.getElementById('career-items');
        if (items && payload.html) {
            items.innerHTML = payload.html;
            document.dispatchEvent(new CustomEvent('career:updated'));
        }

        const options = document.getElementById('achievement-experience-options');
        if (options && payload.experienceOptions) options.innerHTML = payload.experienceOptions;

        if (form.dataset.resetOnSuccess !== undefined) form.reset();
        if (isImport) {
            window.dispatchEvent(new CustomEvent('succeed-loading-modal'));
            window.dispatchEvent(new CustomEvent('import:success'));
        }
        showMessage(payload.message || config.savedText || 'Salvo.');
    } catch (error) {
        if (isImport) {
            importTimers.forEach(clearTimeout);
            window.dispatchEvent(new CustomEvent('fail-loading-modal', { detail: { message: error.message || 'Algo deu errado. Tente novamente.' } }));
            return;
        }
        showErrors([error.message]);
    } finally {
        form.classList.remove('is-loading');
        form.querySelectorAll('button[type="submit"]').forEach((button) => {
            if (button.dataset.originalText) button.textContent = button.dataset.originalText;
        });
    }
});

const closeMobileMenu = () => {
    menu?.classList.remove('is-open');
    menuToggle?.setAttribute('aria-expanded', 'false');
};

document.addEventListener('click', (event) => {
    const sectionLink = event.target.closest('[data-section-link]');
    if (!sectionLink) return;

    closeMobileMenu();
});

const sectionLinks = Array.from(document.querySelectorAll('[data-section-link]'));
const sections = Array.from(document.querySelectorAll('[data-section]'));

if (sectionLinks.length && sections.length && 'IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => {
        const visible = entries
            .filter((entry) => entry.isIntersecting)
            .sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];

        if (!visible) return;

        sectionLinks.forEach((link) => {
            link.classList.toggle('is-active', link.dataset.sectionLink === visible.target.id);
        });
    }, {rootMargin: '-28% 0px -58% 0px', threshold: [0.12, 0.28, 0.5]});

    sections.forEach((section) => observer.observe(section));
}

const activateImportPanel = (target) => {
    const panels = document.querySelectorAll('[data-import-panel]');
    const choices = document.querySelectorAll('[data-import-target]');

    panels.forEach((panel) => {
        panel.hidden = panel.id !== target;
    });

    choices.forEach((choice) => {
        choice.classList.toggle('is-selected', choice.dataset.importTarget === target);
    });

    document.getElementById(target)?.scrollIntoView({behavior: 'smooth', block: 'start'});
};

document.addEventListener('click', (event) => {
    const choice = event.target.closest('[data-import-target]');
    if (!choice) return;

    activateImportPanel(choice.dataset.importTarget);
});

if (document.querySelector('[data-import-panel]')) {
    const initial = window.location.hash?.replace('#', '');
    if (['arquivo', 'colar'].includes(initial)) {
        activateImportPanel(initial);
    }
}
