# Contratos da API

## 1. Informações Gerais

A API seguirá o padrão REST e utilizará JSON para requisições e respostas.

URL base:

`/api/v1`

Endpoints protegidos exigirão autenticação.

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

- status
- priority
- category_id

Exemplo:

GET `/api/v1/tickets?status=open&priority=high`

### Visualizar Chamado

GET `/api/v1/tickets/{id}`

### Criar Chamado

POST `/api/v1/tickets`

Corpo:

{
  "category_id": 1,
  "title": "Computador não inicializa",
  "description": "O computador da sala 103 não está ligando.",
  "priority": "high"
}

O chamado será criado inicialmente com status `open`.

### Atualizar Chamado

PUT `/api/v1/tickets/{id}`

---

## 4. Atendimento

### Assumir Chamado

PATCH `/api/v1/tickets/{id}/assign`

O técnico autenticado será atribuído ao chamado.

### Atribuir Técnico

PATCH `/api/v1/tickets/{id}/technician`

Corpo:

{
  "technician_id": 5
}

Operação destinada ao administrador.

### Alterar Status

PATCH `/api/v1/tickets/{id}/status`

Corpo:

{
  "status": "in_progress"
}

### Resolver Chamado

PATCH `/api/v1/tickets/{id}/resolve`

Corpo:

{
  "solution": "O cabo de alimentação foi substituído."
}

### Fechar Chamado

PATCH `/api/v1/tickets/{id}/close`

---

## 5. Comentários

### Listar Comentários

GET `/api/v1/tickets/{id}/comments`

### Adicionar Comentário

POST `/api/v1/tickets/{id}/comments`

Corpo:

{
  "content": "O problema continua acontecendo após reiniciar."
}

---

## 6. Categorias

### Listar Categorias

GET `/api/v1/categories`

### Criar Categoria

POST `/api/v1/categories`

Corpo:

{
  "name": "Hardware",
  "description": "Problemas relacionados a equipamentos físicos."
}

### Atualizar Categoria

PUT `/api/v1/categories/{id}`

### Desativar Categoria

PATCH `/api/v1/categories/{id}/deactivate`

---

## 7. Status dos Chamados

Os chamados poderão possuir os seguintes estados:

- `open` - Aberto
- `in_progress` - Em atendimento
- `resolved` - Resolvido
- `closed` - Fechado

---

## 8. Prioridades

Os chamados poderão possuir as seguintes prioridades:

- `low` - Baixa
- `medium` - Média
- `high` - Alta

---

## 9. Respostas HTTP

A API utilizará os principais códigos HTTP:

- `200 OK` - Operação realizada com sucesso.
- `201 Created` - Recurso criado com sucesso.
- `204 No Content` - Operação realizada sem conteúdo de resposta.
- `401 Unauthorized` - Usuário não autenticado.
- `403 Forbidden` - Usuário sem permissão.
- `404 Not Found` - Recurso não encontrado.
- `422 Unprocessable Entity` - Dados inválidos ou regra de negócio não atendida.
- `500 Internal Server Error` - Erro interno do servidor.