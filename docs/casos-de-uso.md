# Casos de Uso

## UC01 - Autenticar Usuário

**Ator:** Solicitante, Técnico ou Administrador.

**Pré-condição:** Usuário cadastrado no sistema.

**Fluxo principal:**
1. O usuário informa e-mail e senha.
2. O sistema valida as credenciais.
3. O sistema autentica o usuário.
4. A API retorna os dados do usuário autenticado.

**Fluxo de exceção:**
- Caso as credenciais sejam inválidas, a API retorna erro de autenticação.

---

## UC02 - Abrir Chamado

**Ator:** Solicitante.

**Pré-condição:** Usuário autenticado.

**Fluxo principal:**
1. O solicitante informa título, descrição, categoria e prioridade.
2. O sistema valida os dados.
3. O sistema cria o chamado com status "Aberto".
4. A API retorna o chamado criado.

**Fluxo de exceção:**
- Caso algum dado obrigatório esteja ausente ou inválido, a API retorna erro de validação.

---

## UC03 - Consultar Chamados

**Ator:** Solicitante, Técnico ou Administrador.

**Pré-condição:** Usuário autenticado.

**Fluxo principal:**
1. O usuário solicita a listagem de chamados.
2. O sistema aplica as permissões de acordo com o perfil.
3. O sistema retorna os chamados disponíveis para aquele usuário.

**Regra específica:**
- O solicitante visualiza apenas os chamados criados por ele.

---

## UC04 - Assumir Chamado

**Ator:** Técnico.

**Pré-condição:** Usuário autenticado e chamado disponível.

**Fluxo principal:**
1. O técnico seleciona um chamado aberto.
2. O sistema verifica se o chamado possui técnico responsável.
3. O sistema atribui o técnico autenticado ao chamado.
4. O status do chamado é alterado para "Em atendimento".

**Fluxo de exceção:**
- Caso o chamado já possua técnico responsável, a operação é recusada.

---

## UC05 - Atribuir Técnico

**Ator:** Administrador.

**Pré-condição:** Administrador autenticado.

**Fluxo principal:**
1. O administrador seleciona um chamado.
2. O administrador seleciona um técnico.
3. O sistema valida que o chamado está aberto e sem técnico responsável.
4. O técnico é atribuído ao chamado e o status passa para "Em atendimento".

---

## UC06 - Gerenciar Usuários

**Ator:** Administrador.

**Pré-condição:** Administrador autenticado.

**Fluxo principal:**
1. O administrador acessa a listagem de usuários.
2. O administrador cadastra, edita, desativa ou reativa um usuário.
3. O sistema valida os dados e o perfil selecionado.
4. O sistema salva as informações do usuário.

**Fluxo de exceção:**
- Caso o usuário não seja administrador, a operação é recusada.
- Caso o e-mail já esteja em uso ou o perfil seja inválido, a operação é recusada.
- Um administrador não pode desativar o próprio usuário.

**Regra específica:**
- Não existe cadastro público de usuários.
- Usuários desativados permanecem no histórico, mas não podem autenticar ou usar uma sessão existente.

---

## UC07 - Resolver Chamado

**Ator:** Técnico responsável.

**Pré-condição:** Chamado em atendimento.

**Fluxo principal:**
1. O técnico informa a solução aplicada.
2. O sistema valida se o chamado possui técnico responsável.
3. O sistema valida se a solução foi informada.
4. O chamado é marcado como "Resolvido".

**Fluxo de exceção:**
- Chamados sem técnico responsável não podem ser resolvidos.
- A solução é obrigatória.
- Administradores não podem resolver chamados.

---

## UC08 - Fechar Chamado

**Ator:** Solicitante.

**Pré-condição:** Chamado com status "Resolvido".

**Fluxo principal:**
1. O solicitante acessa o chamado.
2. O solicitante confirma o encerramento.
3. O sistema altera o status para "Fechado".

**Fluxo de exceção:**
- Chamados que ainda não foram resolvidos não podem ser fechados.

---

## UC09 - Adicionar Comentário

**Ator:** Solicitante, Técnico ou Administrador.

**Pré-condição:** Usuário autenticado e com acesso ao chamado.

**Fluxo principal:**
1. O usuário acessa o chamado.
2. O usuário informa o comentário.
3. O sistema valida o conteúdo.
4. O comentário é registrado.

**Fluxo de exceção:**
- Chamados fechados não aceitam novos comentários.

---

## UC10 - Gerenciar Categorias

**Ator:** Administrador.

**Pré-condição:** Administrador autenticado.

**Fluxo principal:**
1. O administrador acessa as categorias.
2. O administrador pode criar, editar ou desativar uma categoria.
3. O sistema valida os dados.
4. A alteração é salva.
