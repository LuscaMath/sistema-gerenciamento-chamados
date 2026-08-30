# Sistema de Gerenciamento de Chamados

Aplicação web para registrar, acompanhar e atender chamados técnicos. O projeto é composto por uma API REST em Laravel e uma SPA em Vue 3 + TypeScript, integradas por autenticação de sessão com Laravel Sanctum.

## Funcionalidades

- Login e logout com cookies de sessão HttpOnly;
- Controle de acesso para solicitantes, técnicos e administradores;
- Gestão administrativa de usuários e categorias, incluindo desativação reversível de usuários;
- Criação, consulta e filtragem de chamados;
- Atribuição de técnico pelo próprio técnico ou manualmente pelo administrador;
- Resolução pelo técnico responsável e fechamento pelo solicitante dono do chamado;
- Comentários em chamados não fechados;
- Painel com resumo dos chamados disponíveis para o perfil autenticado.

## Tecnologias

| Camada | Tecnologias |
| --- | --- |
| Backend | PHP 8.3, Laravel, Sanctum, MySQL, Pest e Scramble/OpenAPI |
| Frontend | Vue 3, TypeScript, Vue Router, Pinia, Axios, Vitest e CSS próprio |

## Pré-requisitos

- PHP 8.3 ou superior;
- Composer;
- MySQL;
- Node.js 22.18 ou superior, ou 24.12 ou superior;
- npm.

## Execução local

### 1. Obter o projeto

```bash
git clone <URL_DO_REPOSITORIO>
cd sistema-gerenciamento-chamados
```

### 2. Configurar o backend

```bash
cd backend
composer install
```

Crie o arquivo de ambiente a partir do exemplo. No PowerShell:

```powershell
Copy-Item .env.example .env
php artisan key:generate
```

Crie o banco no MySQL e ajuste, se necessário, as variáveis `DB_*` em `backend/.env`:

```sql
CREATE DATABASE gerenciamento_chamados;
```

Execute as migrations e os dados de demonstração:

```bash
php artisan migrate --seed
```

Para recriar o banco durante o desenvolvimento:

```bash
php artisan migrate:fresh --seed
```

Inicie a API com o mesmo host definido em `APP_URL`:

```bash
php artisan serve --host=localhost --port=8000
```

### 3. Configurar o frontend

Em outro terminal, a partir da raiz do repositório:

```bash
cd frontend
```

Crie o arquivo de ambiente. No PowerShell:

```powershell
Copy-Item .env.example .env
```

O valor padrão de `VITE_BACKEND_URL` é `http://localhost:8000` e deve corresponder ao endereço da API.

Instale as dependências e inicie a SPA:

```bash
npm install
npm run dev
```

Abra `http://localhost:5173` no navegador.

## Autenticação SPA

O frontend solicita o cookie CSRF antes do login e envia cookies de sessão nas requisições seguintes. Não há tokens armazenados no navegador.

Em ambiente local, mantenha os valores abaixo coerentes no `backend/.env`:

```env
APP_URL=http://localhost:8000
FRONTEND_URLS=http://localhost:5173
SANCTUM_STATEFUL_DOMAINS=localhost:5173
```

Para outro domínio ou porta, atualize os três valores e `VITE_BACKEND_URL` no frontend. Para múltiplas origens permitidas, separe `FRONTEND_URLS` por vírgulas, sem espaços.

## Usuários de demonstração

Os seeders criam usuários somente para desenvolvimento:

| Perfil | E-mail | Senha |
| --- | --- | --- |
| Administrador | `admin@email.com` | `12345678` |
| Técnico | `tecnico@email.com` | `12345678` |
| Solicitante | `requester@email.com` | `12345678` |

Não existe cadastro público. Em uso normal, o administrador cria os demais usuários pela tela **Usuários**.

Usuários desativados permanecem vinculados aos chamados e comentários já registrados, mas não podem fazer login ou utilizar uma sessão existente. Apenas outro administrador pode reativá-los.

## Comandos de validação

Backend:

```bash
cd backend
php artisan test
./vendor/bin/pint --test
```

Frontend:

```bash
cd frontend
npm run lint
npm run type-check
npx vitest run
npm run build
```

## Documentação da API

Com a API em execução, a documentação interativa está em `http://localhost:8000/docs/api` e o documento OpenAPI em `http://localhost:8000/docs/api.json`.

Os contratos e documentos complementares estão em [docs](docs/):

- [API](docs/api.md);
- [Arquitetura](docs/arquitetura.md);
- [Casos de uso](docs/casos-de-uso.md);
- [Escopo](docs/escopo.md);
- [Requisitos](docs/requisitos.md).

## Estrutura

```text
backend/   API Laravel, regras de negócio e testes Pest
frontend/  SPA Vue, testes Vitest e estilos
docs/      Contratos e documentação do projeto
```

