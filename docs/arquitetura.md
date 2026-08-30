# Arquitetura do Sistema

## 1. Visão Geral

O sistema seguirá uma arquitetura cliente-servidor, separando o frontend do backend.

O frontend será responsável pela interface e interação com o usuário, enquanto o backend será responsável pelas regras de negócio, autenticação, autorização e persistência dos dados.

A comunicação entre as aplicações será realizada através de uma API REST utilizando JSON.

## 2. Backend

O backend será desenvolvido com Laravel.

A aplicação será organizada em camadas, separando responsabilidades entre:

- Controllers: recebem e respondem às requisições HTTP.
- Form Requests: validam os dados recebidos pela API.
- Services: concentram as regras de negócio.
- Models: representam as entidades e realizam a interação com o banco através do Eloquent ORM.
- Policies: controlam as permissões de acesso aos recursos.
- API Resources: padronizam os dados retornados pela API.

Fluxo simplificado:

Frontend
↓
API
↓
Controller
↓
Form Request
↓
Service
↓
Model
↓
Banco de Dados

## 3. Frontend

O frontend será desenvolvido com Vue 3 e TypeScript.

Principais tecnologias:

- Vue 3
- Vue Router
- Pinia
- Axios
- CSS próprio com variáveis de design compartilhadas
- Vite
- Vitest
- Vue Test Utils

O Axios será utilizado para realizar as requisições HTTP para a API Laravel.

## 4. Persistência

Será utilizado um banco de dados relacional.

Inicialmente, o projeto utilizará MySQL.

O Laravel Eloquent ORM será responsável pelo mapeamento entre os Models e as tabelas do banco.

## 5. Autenticação

A autenticação da API será implementada utilizando Laravel Sanctum.

Os endpoints protegidos somente poderão ser acessados por usuários autenticados.

As permissões serão definidas de acordo com os perfis:

- Solicitante
- Técnico
- Administrador

## 6. Organização do Repositório

sistema-gerenciamento-chamados/
├── backend/
├── frontend/
├── docs/
├── .gitignore
└── README.md

## 7. Configuração por Ambiente

As configurações dependentes do ambiente serão definidas por variáveis de ambiente.

Exemplos:

Backend:
- Banco de dados
- URL da aplicação
- Credenciais
- Configurações de autenticação

Frontend:
- URL da API

Arquivos `.env` não serão versionados.

## 8. Preparação para DevOps

A separação entre frontend e backend permitirá que cada aplicação seja executada de forma independente.

Na segunda fase do projeto, a arquitetura poderá ser adaptada para utilizar:

- Docker
- Docker Compose
- Nginx
- Pipeline CI/CD
- Ambientes de desenvolvimento e produção
