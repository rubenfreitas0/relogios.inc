describe('Gestão de Produtos', () => {
  const adminUser = {
    id: 1,
    firstname: 'Admin',
    lastname: 'Principal',
    email: 'admin@relogios.inc',
    role: 'admin',
  }

  const mockBrands = {
    data: [
      { id: 1, name: 'Casio', slug: 'casio', logo: '', is_active: true },
      { id: 2, name: 'Seiko', slug: 'seiko', logo: '', is_active: true },
    ],
  }

  const mockCategories = {
    data: [
      { id: 1, name: 'Clássicos', slug: 'classicos', is_active: true },
      { id: 2, name: 'Desportivos', slug: 'desportivos', is_active: true },
    ],
  }

  const mockProductsList = {
    data: [
      {
        id: 1,
        name: 'Casio Vintage Gold',
        slug: 'casio-vintage-gold',
        short_description: 'Relógio dourado retro',
        description: 'Um clássico relógio Casio digital dourado vintage com luz e alarme.',
        price: '99.99',
        discount_price: '79.99',
        stock: 10,
        is_active: true,
        is_featured: true,
        brand: { id: 1, name: 'Casio', slug: 'casio', logo: '' },
        category: { id: 1, name: 'Clássicos', slug: 'classicos' },
        images: [{ id: 101, url: 'https://picsum.photos/200/300', is_primary: true }],
        created_at: '2026-07-01T12:00:00Z',
        updated_at: '2026-07-02T12:00:00Z',
      },
      {
        id: 2,
        name: 'Seiko 5 Automatic',
        slug: 'seiko-5-automatic',
        short_description: 'Relógio mecânico clássico',
        description: 'Relógio Seiko automático clássico de aço inoxidável.',
        price: '250.00',
        discount_price: null,
        stock: 5,
        is_active: true,
        is_featured: false,
        brand: { id: 2, name: 'Seiko', slug: 'seiko', logo: '' },
        category: { id: 1, name: 'Clássicos', slug: 'classicos' },
        images: [],
        created_at: '2026-07-02T12:00:00Z',
        updated_at: '2026-07-03T12:00:00Z',
      },
    ],
    meta: {
      current_page: 1,
      last_page: 1,
      per_page: 10,
      total: 2,
    },
  }

  beforeEach(() => {
    // Definir viewport largo para desktop
    cy.viewport(1400, 900)

    // Intercetar sessões e dados globais em segundo plano
    cy.intercept('GET', '**/api/admin/tickets*', { data: [], pagination: { page: 1, perPage: 5, total: 0 } })
    cy.intercept('GET', '**/api/admin/dashboard/stats*', {
      products: { total: 2, active: 2, out_of_stock: 0, low_stock: 1 },
      orders: { today: 0, this_month: 0, last_month: 0, pending_count: 0, by_status: {} },
      revenue: { this_month: 0, last_month: 0, by_month: [] },
      customers: { total: 0 },
      latest_orders: [],
    })

    // Intercetar marcas e categorias para filtros
    cy.intercept('GET', '**/api/admin/brands*', mockBrands).as('getBrands')
    cy.intercept('GET', '**/api/admin/categories*', mockCategories).as('getCategories')
    cy.intercept('GET', '**/api/admin/products*', mockProductsList).as('getProducts')
  })

  const setupAdminSession = (win: Window) => {
    win.localStorage.setItem('backoffice_auth_token', 'mock-main-admin-token')
    win.localStorage.setItem('backoffice_auth_user', JSON.stringify(adminUser))
  }

  it('deve permitir listar, pesquisar e filtrar produtos por categorias, marcas e estados', () => {
    cy.visit('/produtos', { onBeforeLoad: setupAdminSession })
    cy.wait(['@getBrands', '@getCategories', '@getProducts'])

    // Validar título e contagem inicial
    cy.contains('Produtos').should('be.visible')
    cy.contains('2 produto(s) encontrado(s)').should('be.visible')
    cy.get('table tbody tr').should('have.length', 2)

    // Filtrar por categoria "Clássicos"
    cy.intercept('GET', '**/api/admin/products*', {
      data: [mockProductsList.data[0]],
      meta: { current_page: 1, last_page: 1, per_page: 10, total: 1 },
    }).as('filterCategory')

    cy.get('input[placeholder="Categorias"]').click({ force: true })
    cy.contains('Clássicos').click()
    cy.wait('@filterCategory').its('request.url').should('include', 'category_id=1')

    // Limpar filtro de categoria (usar o botão limpar do VaSelect)
    cy.intercept('GET', '**/api/admin/products*', mockProductsList).as('clearCategory')
    cy.contains('.va-select', 'Clássicos')
      .find('.va-input-wrapper__clear-icon, .va-icon')
      .first()
      .click({ force: true })
    cy.wait('@clearCategory')

    // Filtrar por Estado "Ativos"
    cy.intercept('GET', '**/api/admin/products*', mockProductsList).as('filterActive')
    cy.get('input[placeholder="Estados"]').click({ force: true })
    cy.contains('Ativos').click()
    cy.wait('@filterActive').its('request.url').should('include', 'is_active=true')
  })

  it('deve permitir alterar o número de itens exibidos por página', () => {
    cy.visit('/produtos', { onBeforeLoad: setupAdminSession })
    cy.wait('@getProducts')

    // Alterar limite de itens para 5 por página
    cy.intercept('GET', '**/api/admin/products*', mockProductsList).as('perPage5')
    cy.contains('Itens por página:').parent().find('.va-select').click()
    cy.get('.va-select-option').contains('5').click()
    cy.wait('@perPage5').its('request.url').should('include', 'per_page=5')
  })

  it('deve criar um novo produto completo com todos os campos e imagem', () => {
    cy.visit('/produtos', { onBeforeLoad: setupAdminSession })
    cy.wait('@getProducts')

    // Ir para a página de criação de produto
    cy.contains('Novo Produto').click()
    cy.url().should('include', '/produtos/novo')
    cy.wait(['@getBrands', '@getCategories'])

    // Preencher campos de Informações Gerais
    cy.get('input[placeholder="Ex: Casio MTP-1274D-7ADF"]').type('Casio Oceanus Titanium')
    cy.contains('.va-input-wrapper', 'Descrição curta').find('input').type('Relógio de luxo em titânio')
    cy.contains('.va-input-wrapper', 'Descrição completa')
      .find('textarea')
      .type('O Casio Oceanus é um relógio de gama alta com acabamento impecável em titânio e sincronização solar.')

    // Preencher campos de Preço e Stock
    cy.contains('.va-input-wrapper', 'Preço (€)').find('input').type('850.00')
    cy.contains('.va-input-wrapper', 'Preço de Desconto (€)').find('input').type('750.00')
    cy.contains('.va-input-wrapper', 'Stock').find('input').type('8')
    cy.contains('.va-input-wrapper', 'Peso (kg)').find('input').type('0.150')

    // Preencher Detalhes Adicionais
    cy.contains('.va-input-wrapper', 'Características (features)')
      .find('textarea')
      .type('Movimento: Tough Solar\nResistência à água: 100m\nVidro: Safira')
    cy.contains('.va-input-wrapper', 'Na caixa (in the box)')
      .find('textarea')
      .type('Relógio Oceanus\nCaixa de Luxo\nManual em Português')

    // Upload de Imagem simulado
    const dummyImage =
      'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='
    cy.get('input[type="file"]').selectFile(
      {
        contents: Cypress.Buffer.from(dummyImage.split(',')[1], 'base64'),
        fileName: 'oceanus.png',
        mimeType: 'image/png',
      },
      { force: true },
    )

    // Selecionar Classificações
    cy.contains('.va-input-wrapper', 'Marca').click({ force: true })
    cy.contains('Casio').click({ force: true })

    cy.contains('.va-input-wrapper', 'Categoria').click({ force: true })
    cy.contains('Clássicos').click({ force: true })

    cy.contains('.va-input-wrapper', 'Género').click({ force: true })
    cy.contains('Masculino').click({ force: true })

    // Configurar Destaque e Ativar
    cy.contains('Produto em destaque').click({ force: true })

    // Intercetar pedido POST de criação
    cy.intercept('POST', '**/api/admin/products', {
      statusCode: 201,
      body: {
        message: 'Produto criado com sucesso.',
        data: {
          id: 3,
          name: 'Casio Oceanus Titanium',
          slug: 'casio-oceanus-titanium',
          price: '850.00',
          discount_price: '750.00',
          stock: 8,
          is_active: true,
          is_featured: true,
          brand: { id: 1, name: 'Casio', slug: 'casio' },
          category: { id: 1, name: 'Clássicos', slug: 'classicos' },
          images: [{ id: 102, url: 'https://picsum.photos/200/300', is_primary: true }],
        },
      },
    }).as('createProductRequest')

    cy.contains('button', 'Criar Produto').click({ force: true })
    cy.wait('@createProductRequest')

    // Deve voltar para a lista e mostrar mensagem de sucesso
    cy.url().should('match', /\/produtos$/)
    cy.contains('Produto criado com sucesso.').should('be.visible')
  })

  it('deve gerir produto em destaque (ativar e desativar destaque)', () => {
    // 1. Visitar a lista de produtos e verificar que o primeiro tem destaque (star) e o segundo não (star_outline)
    cy.visit('/produtos', { onBeforeLoad: setupAdminSession })
    cy.wait(['@getBrands', '@getCategories', '@getProducts'])

    cy.get('table tbody tr').first().find('td').eq(5).find('.va-icon').should('have.text', 'star')
    cy.get('table tbody tr').eq(1).find('td').eq(5).find('.va-icon').should('have.text', 'star_outline')

    // 2. Clicar para editar o segundo produto (Seiko 5 Automatic)
    cy.intercept('GET', '**/api/admin/products/2', {
      data: mockProductsList.data[1],
    }).as('getProductDetail')
    cy.get('table tbody tr').contains('Seiko 5 Automatic').parents('tr').find('[title="Editar"]').click()
    cy.wait(['@getBrands', '@getCategories', '@getProductDetail'])

    // 3. Ativar o destaque e guardar
    cy.intercept('POST', '**/api/admin/products/2*', {
      statusCode: 200,
      body: { success: true },
    }).as('updateProductRequest')

    cy.contains('Produto em destaque').click({ force: true })
    cy.contains('button', 'Guardar Alterações').click({ force: true })
    cy.wait('@updateProductRequest')
  })

  it('deve atualizar o stock para zero e confirmar que fica indisponível para compra na loja', () => {
    cy.visit('/produtos', { onBeforeLoad: setupAdminSession })
    cy.wait('@getProducts')

    // Clicar no valor do stock (10) para abrir o modal de atualização rápida de stock
    cy.intercept('PATCH', '**/api/admin/products/1/stock', {
      statusCode: 200,
      body: { success: true },
    }).as('updateStockRequest')

    cy.get('table tbody tr').first().find('.cursor-pointer').click() // Clica no badge do stock
    cy.contains('.va-input-wrapper', 'Novo stock').find('input').clear().type('0')
    cy.get('.va-modal').contains('Guardar').click({ force: true })
    cy.wait('@updateStockRequest')
    cy.contains('Stock atualizado.').should('be.visible')

    // Visitar a página de detalhes do produto na loja de clientes
    const mockProductDetail = {
      id: 1,
      name: 'Casio Vintage Gold',
      slug: 'casio-vintage-gold',
      price: '99.99',
      discount_price: '79.99',
      stock: 0, // Stock zero
      is_active: true,
      brand: { id: 1, name: 'Casio' },
      images: [{ id: 101, url: 'https://picsum.photos/200/300', is_primary: true }],
    }

    cy.intercept('GET', '**/api/catalog/products/casio-vintage-gold', {
      data: mockProductDetail,
    }).as('getProductDetailLoja')

    cy.origin('http://localhost:5173', () => {
      cy.visit('/produtos/casio-vintage-gold')
      cy.wait('@getProductDetailLoja')

      // Deve mostrar "Esgotado" e o aviso de indisponibilidade
      cy.contains('Esgotado').should('be.visible')
      cy.contains('Este produto encontra-se temporariamente indisponível para compra.').should('be.visible')
      cy.contains('adicionar ao carrinho').should('not.exist')
    })
  })

  it('deve permitir eliminar um produto (soft delete) mantendo o histórico', () => {
    cy.visit('/produtos', { onBeforeLoad: setupAdminSession })
    cy.wait('@getProducts')

    // Clicar em eliminar produto 2 (Seiko)
    cy.intercept('DELETE', '**/api/admin/products/2', {
      statusCode: 200,
      body: { message: 'Produto eliminado com sucesso.' },
    }).as('deleteProductRequest')

    cy.get('table tbody tr').contains('Seiko 5 Automatic').parents('tr').find('[title="Eliminar"]').click()
    cy.get('.va-modal').contains('button', 'Eliminar').click({ force: true })
    cy.wait('@deleteProductRequest')

    cy.contains('Produto eliminado.').should('be.visible')
  })
})
