# Contratos da API

## 1. Informações Gerais

A API segue o padrão REST e utiliza JSON para requisições e respostas.

URL base:

`/api/v1`

O frontend é uma SPA autenticada pelo Laravel Sanctum com cookies de sessão HttpOnly. Antes de realizar login, o navegador deve solicitar `GET /sanctum/csrf-cookie`; as requisições seguintes devem enviar cookies e o token XSRF automaticamente. A API não emite nem exige Bearer Token para esse fluxo.

---

## 2. Autenticação

### Inicializar proteção CSRF

GET `/sanctum/csrf-cookie`

Esse endpoint não pertence ao prefixo `/api/v1`. Ele deve ser chamado pelo cliente SPA antes de `login`.

### Realizar Login

POST `/api/v1/login`

Corpo:

{
  "email": "usuario@email.com",
  "password": "senha"
}

### Usuário Autenticado

GET `/api/v1/me`

### Realizar Logout

POST `/api/v1/logout`

---

## 3. Usuários

Todos os endpoints desta seção exigem um administrador autenticado.

### Listar Usuários

GET `/api/v1/users`

### Criar Usuário

POST `/api/v1/users`

Corpo:

{
  "name": "Novo Técnico",
  "email": "tecnico@empresa.com",
  "password": "senha-com-no-minimo-8-caracteres",
  "role": "technician"
}

### Atualizar Usuário

PUT `/api/v1/users/{id}`

Corpo:

{
  "name": "Nome atualizado",
  "email": "usuario@empresa.com",
  "role": "requester"
}

Regras:

- Os perfis aceitos são `requester`, `technician` e `admin`.
- A senha é obrigatória apenas na criação; ao editar, ela pode ser omitida para mantê-la.
- Não existe cadastro público de usuários.

---

## 4. Chamados

### Listar Chamados

GET `/api/v1/tickets`

Filtros opcionais:

- `status`
- `priority`
- `category_id`

Exemplo:

GET `/api/v1/tickets?status=open&priority=high&category_id=1`

Regras de acesso:

- Solicitante visualiza apenas seus próprios chamados.
- Técnico visualiza todos os chamados.
- Administrador visualiza todos os chamados.

### Visualizar Chamado

GET `/api/v1/tickets/{id}`

Regras de acesso:

- Solicitante pode visualizar apenas seus próprios chamados.
- Técnico e administrador podem visualizar qualquer chamado.

### Criar Chamado

POST `/api/v1/tickets`

Corpo:

{
  "category_id": 1,
  "title": "Computador não inicializa",
  "description": "O computador da sala 103 não está ligando.",
  "priority": "high"
}

Regras:

- Apenas solicitantes podem abrir chamados.
- A categoria deve existir e estar ativa.
- O chamado é criado com status `open`.
- O solicitante é obtido através do usuário autenticado.

---

## 5. Atendimento

### Assumir Chamado

PATCH `/api/v1/tickets/{id}/assign`

Não possui corpo.

Regras:

- Apenas técnicos podem assumir chamados.
- O chamado deve estar com status `open`.
- O chamado não pode possuir outro técnico responsável.
- Ao assumir, o status passa para `in_progress`.

### Listar Técnicos Disponíveis

GET `/api/v1/technicians`

Regras:

- Apenas administradores podem consultar a lista.
- A resposta contém somente usuários com perfil `technician`.

### Atribuir Técnico Manualmente

PATCH `/api/v1/tickets/{id}/assign-technician`

Corpo:

{
  "technician_id": 2
}

Regras:

- Apenas administradores podem atribuir um técnico.
- O usuário selecionado deve possuir o perfil `technician`.
- O chamado deve estar aberto e não pode possuir técnico responsável.
- A atribuição altera o status para `in_progress`.

### Resolver Chamado

PATCH `/api/v1/tickets/{id}/resolve`

Corpo:

{
  "solution": "O cabo de alimentação foi substituído."
}

Regras:

- Apenas o técnico responsável pode resolver o chamado.
- O chamado deve estar com status `in_progress`.
- A solução é obrigatória.
- O status passa para `resolved`.
- A data de resolução é registrada.

### Fechar Chamado

PATCH `/api/v1/tickets/{id}/close`

Não possui corpo.

Regras:

- Apenas o solicitante responsável pelo chamado pode fechá-lo.
- O chamado deve estar com status `resolved`.
- O status passa para `closed`.
- A data de fechamento é registrada.

---

## 6. Comentários

### Listar Comentários

GET `/api/v1/tickets/{id}/comments`

Regras:

- O usuário precisa possuir acesso ao chamado.

### Adicionar Comentário

POST `/api/v1/tickets/{id}/comments`

Corpo:

{
  "content": "O problema continua acontecendo após reiniciar."
}

Regras:

- Solicitante pode comentar apenas em seus próprios chamados.
- Técnico e administrador podem comentar em chamados aos quais possuem acesso.
- Chamados fechados não aceitam novos comentários.

---

## 7. Categorias

### Listar Categorias

GET `/api/v1/categories`

### Visualizar Categoria

GET `/api/v1/categories/{id}`

### Criar Categoria

POST `/api/v1/categories`

Corpo:

{
  "name": "Hardware",
  "description": "Problemas relacionados a equipamentos físicos."
}

Regras:

- Apenas administradores podem criar categorias.
- O nome deve ser único.

### Atualizar Categoria

PUT ou PATCH `/api/v1/categories/{id}`

Corpo:

{
  "name": "Hardware e Equipamentos",
  "description": "Problemas relacionados a equipamentos físicos."
}

Regras:

- Apenas administradores podem atualizar categorias.

### Desativar Categoria

PATCH `/api/v1/categories/{id}/deactivate`

Não possui corpo.

Regras:

- Apenas administradores podem desativar categorias.
- Categorias desativadas não podem ser utilizadas em novos chamados.

### Ativar Categoria

PATCH `/api/v1/categories/{id}/activate`

Não possui corpo.

Regras:

- Apenas administradores podem reativar categorias.

---

## 8. Status dos Chamados

- `open` - Aberto
- `in_progress` - Em atendimento
- `resolved` - Resolvido
- `closed` - Fechado

Fluxo principal:

`open → in_progress → resolved → closed`

---

## 9. Prioridades

- `low` - Baixa
- `medium` - Média
- `high` - Alta

---

## 10. Perfis de Usuário

- `requester` - Solicitante
- `technician` - Técnico
- `admin` - Administrador

---

## 11. Respostas HTTP

- `200 OK` - Operação realizada com sucesso.
- `201 Created` - Recurso criado com sucesso.
- `401 Unauthorized` - Usuário não autenticado.
- `403 Forbidden` - Usuário sem permissão para executar a operação.
- `404 Not Found` - Recurso não encontrado.
- `422 Unprocessable Entity` - Dados inválidos ou regra de negócio não atendida.
- `500 Internal Server Error` - Erro interno inesperado.
