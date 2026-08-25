# Contratos da API

## 1. Informações Gerais

A API segue o padrão REST e utiliza JSON para requisições e respostas.

URL base:

`/api/v1`

Endpoints protegidos exigem autenticação via Laravel Sanctum utilizando Bearer Token.

---

## 2. Autenticação

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

## 3. Chamados

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

## 4. Atendimento

### Assumir Chamado

PATCH `/api/v1/tickets/{id}/assign`

Não possui corpo.

Regras:

- Apenas técnicos podem assumir chamados.
- O chamado deve estar com status `open`.
- O chamado não pode possuir outro técnico responsável.
- Ao assumir, o status passa para `in_progress`.

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

## 5. Comentários

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

## 6. Categorias

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

## 7. Status dos Chamados

- `open` - Aberto
- `in_progress` - Em atendimento
- `resolved` - Resolvido
- `closed` - Fechado

Fluxo principal:

`open → in_progress → resolved → closed`

---

## 8. Prioridades

- `low` - Baixa
- `medium` - Média
- `high` - Alta

---

## 9. Perfis de Usuário

- `requester` - Solicitante
- `technician` - Técnico
- `admin` - Administrador

---

## 10. Respostas HTTP

- `200 OK` - Operação realizada com sucesso.
- `201 Created` - Recurso criado com sucesso.
- `401 Unauthorized` - Usuário não autenticado.
- `403 Forbidden` - Usuário sem permissão para executar a operação.
- `404 Not Found` - Recurso não encontrado.
- `422 Unprocessable Entity` - Dados inválidos ou regra de negócio não atendida.
- `500 Internal Server Error` - Erro interno inesperado.