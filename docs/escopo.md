# Definição de Escopo

## 1. Objetivo do Sistema

O Sistema de Gerenciamento de Chamados tem como objetivo permitir o registro, acompanhamento e atendimento de chamados técnicos por meio de uma aplicação web composta por frontend e backend independentes, integrados através de uma API REST.

## 2. Usuários do Sistema

### Solicitante
Usuário responsável por abrir chamados e acompanhar o andamento das solicitações.

### Técnico
Usuário responsável por assumir, atender e resolver chamados.

### Administrador
Usuário responsável pelo gerenciamento de categorias, usuários e acompanhamento geral dos chamados.

## 3. Escopo Inicial

O sistema deverá permitir:

- Autenticação de usuários;
- Cadastro e gerenciamento de categorias;
- Cadastro e gerenciamento de usuários por administradores;
- Abertura de chamados;
- Listagem e consulta de chamados;
- Atribuição de técnicos;
- Registro da solução;
- Adição de comentários;
- Painel com resumo dos chamados disponíveis para o perfil autenticado;
- Controle de permissões por perfil;
- Validações de regras de negócio.

## 4. Fora do Escopo Inicial

Neste primeiro momento, não serão implementados:

- Notificações;
- Anexos;
- Relatórios avançados;
- Avaliação de atendimento;
- Histórico detalhado de alterações.

Essas funcionalidades poderão ser adicionadas futuramente.
