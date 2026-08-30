# Requisitos do Sistema

## 1. Requisitos Funcionais

### RF01 - Autenticar Usuário
O sistema deve permitir que usuários cadastrados realizem login e logout.

### RF02 - Gerenciar Categorias
O administrador deve poder cadastrar, editar, listar e desativar categorias de chamados.

### RF03 - Abrir Chamado
O solicitante deve poder abrir um chamado informando título, descrição, categoria e prioridade.

### RF04 - Consultar Chamados
O sistema deve permitir a consulta de chamados de acordo com as permissões do usuário.

### RF05 - Filtrar Chamados
O sistema deve permitir a filtragem de chamados por status, prioridade e categoria.

### RF06 - Atribuir Técnico
O técnico deve poder assumir um chamado disponível e o administrador deve poder atribuir um chamado a um técnico.

### RF07 - Registrar Solução
O técnico responsável deve poder registrar a solução aplicada e marcar o chamado como resolvido.

### RF08 - Adicionar Comentários
Solicitantes, técnicos e administradores devem poder adicionar comentários aos chamados aos quais possuem acesso.

### RF09 - Fechar Chamado
O solicitante deve poder fechar um chamado após sua resolução.

### RF10 - Gerenciar Usuários
O administrador deve poder listar, cadastrar e editar usuários, definindo os perfis de solicitante, técnico ou administrador.

## 2. Requisitos Não Funcionais

### RNF01 - API REST
A comunicação entre frontend e backend deve ocorrer através de uma API REST utilizando JSON.

### RNF02 - Persistência
Os dados da aplicação devem ser armazenados em banco de dados relacional.

### RNF03 - Autenticação e Autorização
A API deve proteger os recursos que exigem autenticação e controlar o acesso de acordo com o perfil do usuário.

### RNF04 - Validação
Os dados recebidos pela API devem ser validados antes de serem processados.

### RNF05 - Configuração por Ambiente
Credenciais e configurações dependentes do ambiente devem ser definidas através de variáveis de ambiente.

### RNF06 - Testes Automatizados
O sistema deve possuir testes automatizados para validar funcionalidades e regras de negócio principais.

### RNF07 - Responsividade
A interface web deve ser utilizável em diferentes tamanhos de tela.

## 3. Regras de Negócio

### RN01 - Atribuição de Chamados
Um chamado não pode ser assumido por outro técnico enquanto já possuir um técnico responsável.

### RN02 - Atendimento
Somente o técnico responsável pode resolver um chamado em atendimento. O administrador pode consultar chamados e atribuir um técnico, mas não pode assumi-los ou resolvê-los.

### RN03 - Resolução
Um chamado somente pode ser marcado como resolvido quando possuir um técnico responsável e uma descrição da solução.

### RN04 - Fechamento
Somente chamados resolvidos podem ser fechados.

### RN05 - Alteração de Chamado Fechado
Chamados fechados não podem receber alterações.

### RN06 - Acesso do Solicitante
O solicitante pode consultar e interagir apenas com chamados criados por ele.

### RN07 - Gerenciamento de Categorias
Somente administradores podem cadastrar, editar ou desativar categorias.

### RN08 - Gerenciamento de Usuários
Somente administradores podem listar, cadastrar, editar, desativar ou reativar usuários. Não existe cadastro público, e usuários desativados não podem acessar o sistema.
