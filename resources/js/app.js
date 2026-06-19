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
    const items = list.map((item) => `<li>${item}</li>`).join('');
    box.innerHTML = `<strong>${config.reviewDataText || 'Revise os dados:'}</strong><ul>${items}</ul>`;
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

        const payload = await response.json();

        if (!response.ok) {
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
        showMessage(payload.message || config.savedText || 'Salvo.');
    } catch (error) {
        showErrors([error.message]);
    } finally {
        form.classList.remove('is-loading');
        form.querySelectorAll('button[type="submit"]').forEach((button) => {
            if (button.dataset.originalText) button.textContent = button.dataset.originalText;
        });
    }
});
