# Sistema de Gerenciamento de Chamados

Sistema web para registro, acompanhamento e atendimento de chamados técnicos, desenvolvido para a disciplina **Optativa II**.

O projeto utiliza uma arquitetura separada entre **backend e frontend**, onde o backend disponibiliza uma API REST e o frontend consome seus recursos.

## Funcionalidades

Atualmente o sistema possui:

* Autenticação via API;
* Controle de acesso por perfil de usuário;
* Gerenciamento de categorias;
* Abertura e consulta de chamados;
* Filtros por status, prioridade e categoria;
* Atribuição de chamados a técnicos;
* Resolução e fechamento de chamados;
* Comentários em chamados;
* Validação de regras de negócio;
* Documentação da API com OpenAPI/Swagger;
* Testes automatizados.

### Perfis de usuário

O sistema possui três perfis:

* **Solicitante (`requester`)**: abre e acompanha seus chamados;
* **Técnico (`technician`)**: acompanha e atende chamados;
* **Administrador (`admin`)**: possui acesso administrativo ao sistema.

## Tecnologias

### Backend

* PHP
* Laravel
* Laravel Sanctum
* MySQL
* Pest
* Scramble / OpenAPI

### Frontend

* Vue 3
* TypeScript
* Axios
* Pinia
* Tailwind CSS
* Vitest

## Estrutura do Projeto

```text
sistema-gerenciamento-chamados/
├── backend/       # API REST desenvolvida em Laravel
├── frontend/      # Aplicação cliente desenvolvida em Vue
├── docs/          # Documentação do sistema
├── .gitignore
└── README.md
```

## Executando o Backend

### Pré-requisitos

Para executar o backend é necessário possuir:

* PHP
* Composer
* MySQL

### 1. Clonar o repositório

```bash
git clone <URL_DO_REPOSITORIO>
cd sistema-gerenciamento-chamados
```

### 2. Instalar as dependências

```bash
cd backend
composer install
```

### 3. Configurar o ambiente

Copie o arquivo `.env.example`:

```bash
cp .env.example .env
```

No Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

Gere a chave da aplicação:

```bash
php artisan key:generate
```

### 4. Configurar o banco de dados

Crie um banco MySQL:

```sql
CREATE DATABASE gerenciamento_chamados;
```

Configure o `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gerenciamento_chamados
DB_USERNAME=root
DB_PASSWORD=
```

Ajuste usuário, senha e porta de acordo com seu ambiente.

### 5. Criar e popular o banco

```bash
php artisan migrate --seed
```

Para recriar completamente o banco durante o desenvolvimento:

```bash
php artisan migrate:fresh --seed
```

### 6. Executar a API

```bash
php artisan serve
```

Por padrão, a aplicação estará disponível em:

```text
http://127.0.0.1:8000
```

A URL base da API é:

```text
http://127.0.0.1:8000/api/v1
```

## Usuários de Desenvolvimento

Os seeders criam os seguintes usuários:

| Perfil        | E-mail                | Senha      |
| ------------- | --------------------- | ---------- |
| Administrador | `admin@email.com`     | `12345678` |
| Técnico       | `tecnico@email.com`   | `12345678` |
| Solicitante   | `requester@email.com` | `12345678` |

Essas credenciais são destinadas exclusivamente ao ambiente de desenvolvimento.

## Documentação da API

A documentação interativa da API é gerada através do Scramble/OpenAPI.

Com o backend em execução, acesse:

```text
http://127.0.0.1:8000/docs/api
```

O documento OpenAPI também está disponível em:

```text
http://127.0.0.1:8000/docs/api.json
```

A documentação complementar dos contratos da API está disponível em:

```text
docs/api.md
```

## Testes

Os testes automatizados utilizam Pest.

Para executar toda a suíte:

```bash
php artisan test
```

Ou diretamente pelo Pest:

```bash
./vendor/bin/pest
```

No Windows PowerShell:

```powershell
.\vendor\bin\pest
```

Os testes cobrem, entre outros cenários:

* Permissões de usuários;
* Gerenciamento de categorias;
* Criação e visualização de chamados;
* Atribuição de técnicos;
* Resolução e fechamento;
* Comentários;
* Filtros de chamados;
* Regras de negócio.

## Fluxo dos Chamados

O fluxo principal de um chamado é:

```text
Aberto
  ↓
Em atendimento
  ↓
Resolvido
  ↓
Fechado
```

Internamente:

```text
open → in_progress → resolved → closed
```

## Arquitetura

O backend utiliza uma organização baseada na separação de responsabilidades:

```text
Request HTTP
    ↓
Form Request
    ↓
Controller
    ↓
Service
    ↓
Model / Eloquent
    ↓
Banco de Dados
```

Além disso:

* **Form Requests** realizam validações de entrada;
* **Controllers** coordenam requisições e respostas;
* **Services** concentram regras de negócio;
* **Policies** controlam autorização;
* **Models** representam as entidades;
* **API Resources** padronizam as respostas JSON;
* **Enums** representam estados e valores controlados.

Mais detalhes estão disponíveis em:

```text
docs/arquitetura.md
```

## Documentação

A pasta `docs/` contém a documentação complementar do projeto:

```text
docs/
├── api.md
├── arquitetura.md
├── casos-de-uso.md
├── escopo.md
└── requisitos.md
```

## Frontend

O frontend será desenvolvido separadamente utilizando Vue 3 e TypeScript e consumirá a API REST disponibilizada pelo Laravel.

As instruções específicas para execução do frontend serão adicionadas conforme seu desenvolvimento.

## DevOps

A arquitetura separada entre frontend e backend também prepara o projeto para a etapa de DevOps, que poderá incluir:

* Docker;
* Docker Compose;
* Nginx;
* CI/CD;
* GitLab CI/CD;
* Ambientes separados de desenvolvimento e produção.
