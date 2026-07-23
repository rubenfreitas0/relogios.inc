# Relogios.inc

Plataforma de **e-commerce de relógios** full-stack, composta por três aplicações que comunicam entre si através de uma API REST única:

| Aplicação | Descrição | Tecnologia |
|-----------|-----------|------------|
| **`api/`** | API REST — núcleo de dados e regras de negócio | Laravel 12 · PHP 8.2 · PostgreSQL |
| **`loja/`** | Loja online (frontend do cliente) | Vue 3 · TypeScript · Pinia · Tailwind |
| **`backoffice/`** | Painel de administração | Vue 3 · TypeScript · Vuestic UI |

A **API é a única fonte de verdade**: tanto a loja como o backoffice consomem-na exclusivamente via REST, com autenticação por tokens (Laravel Sanctum). Nenhum frontend acede diretamente à base de dados.

---

## Índice

- [Arquitetura](#arquitetura)
- [Funcionalidades](#funcionalidades)
- [Stack tecnológica](#stack-tecnológica)
- [Base de dados](#base-de-dados)
- [Como executar](#como-executar)
- [Testes](#testes)
- [Estrutura do repositório](#estrutura-do-repositório)

---

## Arquitetura

```
┌──────────────┐        ┌──────────────┐
│     Loja     │        │  Backoffice  │
│   (Vue 3)    │        │   (Vue 3)    │
└──────┬───────┘        └───────┬──────┘
       │  REST / JSON           │  REST / JSON
       │  (Bearer token)        │  (Bearer token)
       └───────────┬────────────┘
                   ▼
          ┌─────────────────┐
          │       API       │
          │   (Laravel 12)  │
          │  56 endpoints   │
          └────────┬────────┘
                   ▼
          ┌─────────────────┐
          │   PostgreSQL    │
          │ views·triggers· │
          │    funções      │
          └─────────────────┘
```

---

## Funcionalidades

### Loja (cliente)

- **Catálogo** com navegação por marca e categoria, filtros, produtos em destaque e relacionados
- **Autenticação** completa: registo, verificação de email, login e reset de password
- **Carrinho persistente** guardado na base de dados por utilizador, com validação de stock
- **Checkout** guiado — morada, método de envio e pagamento num fluxo único
- **Pagamentos**: MB Way, Multibanco, cartão de crédito, Apple Pay e Google Pay (confirmação por webhook)
- **Área de cliente**: encomendas, moradas e dados pessoais em self-service
- **Suporte** através de tickets
- Design responsivo *mobile-first* e produtos vistos recentemente

### Backoffice (administração)

- **Dashboard** com KPIs de receita, encomendas, clientes e stock em baixa
- **Gestão de catálogo**: produtos, marcas e categorias (com imagens e *soft delete*/restauro)
- **Encomendas e envios**: estados, notas internas, métodos e zonas de envio configuráveis
- **Preços e promoções**: descontos por produto, com histórico de preços registado automaticamente
- **Relatórios** construídos sobre *views* SQL (receita mensal, por região, *lifetime value*)
- **Vitrine configurável**: *hero* e destaques da loja editáveis sem alterar código
- **Tickets** de suporte e **gestão de utilizadores**

### Segurança

- Autenticação por tokens (Sanctum) com verificação de email obrigatória antes da primeira compra
- Controlo de acessos por papéis (RBAC) via middleware de administração
- *Rate limiting* no login, registo e reset de password
- Checkout atómico com transações e *locks* pessimistas — sem *overselling* nem *race conditions*

---

## Stack tecnológica

**Backend (`api/`)**
- Laravel 12, PHP 8.2
- PostgreSQL (Eloquent ORM)
- Laravel Sanctum (autenticação)
- PHPUnit (testes de feature)

**Frontends (`loja/` e `backoffice/`)**
- Vue 3 + TypeScript
- Pinia (estado) · Vue Router · Tailwind CSS
- Vuestic UI (backoffice)
- Vite (build) · Cypress (testes E2E)

---

## Base de dados

Modelação em **PostgreSQL**, com lógica próxima dos dados:

- **16 modelos** Eloquent (produtos, marcas, categorias, encomendas, pagamentos, envios, tickets, …)
- **32 migrações** — evolução versionada do esquema
- **4 views SQL** — receita mensal, receita por região, *customer lifetime value* e estado de stock
- **Triggers** — registo automático do histórico de preços a cada alteração
- **Funções** — cálculo de totais de encomenda e aplicação de descontos em lote
- **Enums de domínio** — estados de encomenda (`pending → processing → shipped → delivered / cancelled / refunded`) e de pagamento
- **17 seeders** — dados de demonstração realistas, incluindo histórico para relatórios

---

## Como executar

> **Pré-requisitos:** PHP 8.2+, Composer, Node.js 18+, PostgreSQL

### 1. API (`api/`)

```bash
cd api
cp .env.example .env          # ajusta as credenciais de DB_/MAIL_
composer install
php artisan key:generate
php artisan migrate --seed     # cria o esquema e popula dados de demonstração
composer run dev               # servidor, queue, logs e vite em simultâneo
```

A API fica disponível em `http://localhost:8000` (endpoints sob `/api`).

Configuração relevante no `.env`:

```env
DB_CONNECTION=pgsql
DB_DATABASE=relogios_inc
DB_USERNAME=postgres
DB_PASSWORD=postgres
FRONTEND_URL=http://localhost:5173
```

### 2. Loja (`loja/`)

```bash
cd loja
npm install
npm run dev
```

Disponível em `http://localhost:5173`. As chamadas a `/api` são encaminhadas (proxy) para `http://localhost:8000`.

### 3. Backoffice (`backoffice/`)

```bash
cd backoffice
cp .env.example .env          # VITE_API_BASE_URL=http://localhost:8000/api
npm install
npm run dev
```

---

## Testes

**API — PHPUnit** (92 testes, 297 asserções)

```bash
cd api
php artisan test
```

Cobre autenticação, catálogo, carrinho, checkout, moradas, envios, pagamentos, administração e suporte. Cada teste corre em base de dados limpa, com dados gerados por *factories*.

**Frontends — Cypress** (23 suites E2E)

```bash
# na loja ou no backoffice
npm run cypress:open     # modo interativo
npm run cypress:run      # modo headless
```

---

## Estrutura do repositório

```
relogios.inc/
├── api/            # API REST — Laravel 12 + PostgreSQL
│   ├── app/        # Models, Controllers, Services, Enums, Requests
│   ├── database/   # migrações, seeders, factories
│   ├── routes/     # api.php (56 rotas)
│   └── tests/      # PHPUnit (Feature)
├── loja/           # Loja online — Vue 3 + TypeScript
│   ├── src/        # pages, components, pinia, routes
│   └── cypress/    # testes E2E
└── backoffice/     # Painel de administração — Vue 3 + Vuestic UI
    ├── src/        # pages, components
    └── cypress/    # testes E2E
```

---

<p align="center"><sub>Projeto Relogios.inc · 2026</sub></p>
