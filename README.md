# Vyrko

MVP SaaS em Laravel para criar currículos personalizados por vaga sem inventar dados profissionais.

## Stack

- Laravel 13
- Laravel Sail
- MySQL 8.4, Redis e Mailpit
- Blade, Vite e Tailwind CSS
- IA via Gemini por padrão com `AI_PROVIDER=gemini`

## MVP entregue

- Autenticação simples com cadastro, login e logout
- Dashboard autenticado com métricas de vagas e currículos
- Inventário de Carreira com perfil, experiências, conquistas, habilidades, projetos, formações, certificações e idiomas
- Cadastro e análise de vagas com JSON estruturado
- Relatório de match com score dividido, gaps, recomendações e mapa de evidências
- Geração de currículo em JSON e texto copiável usando apenas evidências do inventário
- Checklist ATS salvo em `resume_versions.ats_checklist`
- Análise de LinkedIn via IA, sem scraping
- Limites simples de uso e logs de IA

## Como rodar

```bash
composer install
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate --seed
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
```

Aplicação: http://localhost

Mailpit: http://localhost:8025

Usuário dev opcional criado pelo seed quando `SEED_DEV_USER=true`:

```text
dev@vyrko.test
password
```

## Qualidade

```bash
php artisan test
./vendor/bin/pint --test
```

## Comandos úteis

```bash
./vendor/bin/sail artisan test
./vendor/bin/sail artisan tinker
./vendor/bin/sail down
```
