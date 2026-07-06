describe('Gestão de Utilizadores', () => {
  const mainAdminUser = {
    id: 1,
    firstname: 'Admin',
    lastname: 'Principal',
    email: 'admin@relogios.inc',
    role: 'admin',
  }

  const secondaryAdminUser = {
    id: 3,
    firstname: 'Admin',
    lastname: 'Secundário',
    email: 'secundario@relogios.inc',
    role: 'admin',
  }

  const clientUser = {
    id: 2,
    firstname: 'João',
    lastname: 'Cliente',
    email: 'cliente@relogios.inc',
    role: 'user',
  }

  const mockUsersList = {
    data: [
      {
        id: 1,
        fullname: 'Admin Principal',
        email: 'admin@relogios.inc',
        role: 'admin',
        active: true,
        username: 'admin',
        avatar: '',
        notes: 'Administrador principal do sistema',
      },
      {
        id: 4,
        fullname: 'Carlos Vendedor',
        email: 'carlos@relogios.inc',
        role: 'admin',
        active: true,
        username: 'carlos',
        avatar: '',
        notes: 'Staff de vendas',
      },
      {
        id: 5,
        fullname: 'Maria Clienta',
        email: 'maria@gmail.com',
        role: 'user',
        active: false,
        username: 'maria',
        avatar: '',
        notes: 'Cliente registado',
      },
    ],
    pagination: {
      page: 1,
      perPage: 10,
      total: 3,
    },
  }

  beforeEach(() => {
    // Definir um viewport amplo para assegurar que a sidebar está aberta e os itens visíveis
    cy.viewport(1400, 900)

    // Intercetar pedidos globais em segundo plano para evitar 401s ao simular tokens fictícios
    cy.intercept('GET', '/api/admin/tickets*', {
      data: [],
      pagination: { page: 1, perPage: 5, total: 0 },
    }).as('globalTickets')

    cy.intercept('GET', '/api/admin/dashboard/stats*', {
      products: { total: 0, active: 0, out_of_stock: 0, low_stock: 0 },
      orders: { today: 0, this_month: 0, last_month: 0, pending_count: 0, by_status: {} },
      revenue: { this_month: 0, last_month: 0, by_month: [] },
      customers: { total: 0 },
      latest_orders: [],
    }).as('globalStats')
  })

  it('deve bloquear login de um cliente sem permissões e mostrar mensagem de erro', () => {
    cy.visit('/auth/login')

    // Intercetar login para retornar utilizador normal (user/customer)
    cy.intercept('POST', '/api/login', {
      statusCode: 200,
      body: {
        token: 'mock-client-token',
        user: clientUser,
      },
    }).as('loginRequest')

    cy.get('input[type="email"]').type('cliente@relogios.inc')
    cy.get('input[type="password"]').type('password')
    cy.get('button').contains('Entrar').click()

    cy.wait('@loginRequest')

    // Deve permanecer na página de login e mostrar o alerta de erro
    cy.url().should('include', '/auth/login')
    cy.contains('Acesso restrito a administradores.').should('be.visible')
  })

  it('deve redirecionar para o login se um cliente sem permissões tentar aceder diretamente', () => {
    // Simular utilizador cliente autenticado persistido no localStorage
    cy.visit('/auth/login', {
      onBeforeLoad(win) {
        win.localStorage.setItem('backoffice_auth_token', 'mock-client-token')
        win.localStorage.setItem('backoffice_auth_user', JSON.stringify(clientUser))
      },
    })

    // Tentar visitar o dashboard diretamente
    cy.visit('/dashboard')

    // O router beforeEach deve detetar que o papel não é admin, fazer logout e redirecionar
    cy.url().should('include', '/auth/login')
  })

  it('deve mostrar menus restritos ao Administrador Principal e ocultá-los de outros administradores', () => {
    // 1. Administrador Principal (admin@relogios.inc)
    cy.visit('/auth/login', {
      onBeforeLoad(win) {
        win.localStorage.setItem('backoffice_auth_token', 'mock-main-admin-token')
        win.localStorage.setItem('backoffice_auth_user', JSON.stringify(mainAdminUser))
      },
    })
    cy.visit('/dashboard')

    // Deve ver todos os menus na sidebar
    cy.contains('Utilizadores').should('be.visible')
    cy.contains('Relatórios').should('be.visible')
    cy.contains('Vitrine').should('be.visible')

    // 2. Administrador Secundário (outro email)
    cy.visit('/auth/login', {
      onBeforeLoad(win) {
        win.localStorage.setItem('backoffice_auth_token', 'mock-secondary-admin-token')
        win.localStorage.setItem('backoffice_auth_user', JSON.stringify(secondaryAdminUser))
      },
    })
    cy.visit('/dashboard')

    // NÃO deve ver os menus restritos na sidebar
    cy.contains('Utilizadores').should('not.exist')
    cy.contains('Relatórios').should('not.exist')
    cy.contains('Vitrine').should('not.exist')

    // Tentar aceder diretamente via URL a um menu restrito (/users) deve redirecionar para o dashboard
    cy.visit('/users')
    cy.url().should('include', '/dashboard')
  })

  it('deve permitir listar, pesquisar, criar e desativar utilizadores no Administrador Principal', () => {
    // Logar como Administrador Principal
    cy.visit('/auth/login', {
      onBeforeLoad(win) {
        win.localStorage.setItem('backoffice_auth_token', 'mock-main-admin-token')
        win.localStorage.setItem('backoffice_auth_user', JSON.stringify(mainAdminUser))
      },
    })

    // Intercetar listagem e criação
    cy.intercept('GET', '/api/admin/users*', mockUsersList).as('getUsers')
    cy.intercept('POST', '/api/admin/users', {
      statusCode: 201,
      body: {
        id: 6,
        fullname: 'Novo Administrador',
        email: 'novo@relogios.inc',
        role: 'admin',
        active: true,
        username: 'novo',
        avatar: '',
        notes: 'Novo admin criado via E2E',
      },
    }).as('createUser')

    cy.visit('/users')
    cy.wait('@getUsers')

    cy.contains('Utilizadores').should('be.visible')
    cy.get('table tbody tr').should('have.length', 3)

    // Barra de Pesquisa: intercetar especificamente a pesquisa por "carlos"
    cy.intercept('GET', '**/api/admin/users?*search=carlos*').as('searchCarlos')
    cy.get('input[placeholder="Pesquisar"]').type('carlos')
    cy.wait('@searchCarlos')

    // Limpar pesquisa
    cy.get('input[placeholder="Pesquisar"]').clear()
    cy.wait('@getUsers')

    // Criar Utilizador: abrir modal
    cy.contains('Adicionar Utilizador').click()

    // Validar termos em Português
    cy.contains('Nome completo').should('exist')
    cy.contains('Nome de utilizador').should('exist')
    cy.contains('Email').should('exist')
    cy.contains('Palavra-passe').should('exist')
    cy.contains('Função').should('exist')
    cy.contains('Ativo').should('exist')
    cy.contains('Notas').should('exist')

    // Preencher formulário de criação com requisitos de palavra-passe
    cy.get('input[name="fullName"]').type('Novo Administrador')
    cy.get('input[name="username"]').type('novo')
    cy.get('input[name="email"]').type('novo@relogios.inc')

    // Testar validação de password curta (< 8 chars)
    cy.get('input[name="password"]').type('1234').blur()
    cy.contains('A palavra-passe deve ter pelo menos 8 caracteres').should('be.visible')

    // Colocar password correta
    cy.get('input[name="password"]').clear().type('superpassword123')

    // Selecionar função
    cy.get('.va-modal .va-select').click()
    cy.contains('Administrador').click()

    cy.get('.va-modal').contains('button', 'Adicionar').click()
    cy.wait('@createUser')

    cy.contains('O utilizador Novo Administrador foi criado').should('be.visible')

    // Desativar Utilizador: Editar Carlos
    cy.intercept('PUT', '/api/admin/users/4', {
      statusCode: 200,
      body: {
        id: 4,
        fullname: 'Carlos Vendedor',
        email: 'carlos@relogios.inc',
        role: 'admin',
        active: false,
        username: 'carlos',
        avatar: '',
        notes: 'Staff de vendas',
      },
    }).as('updateUser')

    cy.get('table tbody tr').contains('Carlos Vendedor').parents('tr').find('[aria-label="Edit user"]').click()

    cy.contains('Editar utilizador').should('be.visible')
    // Desmarcar checkbox "Ativo"
    cy.get('.va-modal .va-checkbox').click()

    cy.get('.va-modal').contains('button', 'Guardar').click()
    cy.wait('@updateUser').its('request.body.active').should('eq', false)
    cy.contains('O utilizador Carlos Vendedor foi atualizado').should('be.visible')
  })
})
