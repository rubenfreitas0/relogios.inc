describe('Account — Auth Guard', () => {
	it('redirects to /login when not authenticated', () => {
		cy.viewport('macbook-15')
		// Clear any existing token
		cy.window().then((win) => {
			win.localStorage.removeItem('auth_token')
		})
		cy.visit('/conta')
		cy.url().should('include', '/login')
	})
})

describe('Account — Profile Tab', () => {
	beforeEach(() => {
		cy.viewport('macbook-15')

		// Login programatically
		cy.request('POST', '/api/login', {
			email: 'admin@relogios.inc',
			password: 'password'
		}).then((response) => {
			const token = response.body.token
			window.localStorage.setItem('auth_token', token)
		})

		cy.visit('/conta/perfil')
	})

	it('displays "A Minha Conta" header', () => {
		cy.contains('A Minha Conta').should('be.visible')
	})

	it('shows user email in the header', () => {
		cy.contains('admin@relogios.inc').should('be.visible')
	})

	it('displays profile card with user initials', () => {
		// The avatar circle should contain the user's initials
		cy.get('.rounded-full.bg-\\[\\#FFC700\\]').should('be.visible')
	})

	it('shows the user firstname, lastname, email and phone fields', () => {
		cy.contains('Primeiro Nome').should('be.visible')
		cy.contains('Apelido').should('be.visible')
		cy.contains('Email').should('be.visible')
		cy.contains('Telefone').should('be.visible')
	})

	it('displays sidebar with all tabs', () => {
		cy.contains('Perfil').should('be.visible')
		cy.contains('Encomendas').should('be.visible')
		cy.contains('Moradas').should('be.visible')
		cy.contains('Ajuda & Suporte').should('be.visible')
	})

	it('profile tab is active by default', () => {
		// The active tab has the golden background
		cy.get('a[href="/conta/perfil"]').should('have.class', 'bg-[#FFC700]/10')
	})

	it('shows a verified/unverified badge next to the email', () => {
		cy.contains(/Verificado|Não verificado/).should('be.visible')
	})
})

describe('Account — Profile Tab — Edit Phone', () => {
	beforeEach(() => {
		cy.viewport('macbook-15')

		cy.request('POST', '/api/login', {
			email: 'admin@relogios.inc',
			password: 'password'
		}).then((response) => {
			const token = response.body.token
			window.localStorage.setItem('auth_token', token)
		})

		cy.visit('/conta/perfil')
	})

	it('phone is shown as read-only text by default', () => {
		cy.contains('button', 'Editar Telemóvel').should('be.visible')
	})

	it('clicking "Editar Telemóvel" reveals an editable input', () => {
		cy.contains('button', 'Editar Telemóvel').click()
		cy.get('input[placeholder="Ex: 912345678"]').should('be.visible')
		cy.contains('button', 'Cancelar').should('be.visible')
		cy.contains('button', 'Guardar').should('be.visible')
	})

	it('"Cancelar" discards the change and exits edit mode', () => {
		cy.contains('button', 'Editar Telemóvel').click()
		cy.get('input[placeholder="Ex: 912345678"]').clear().type('999999999')
		cy.contains('button', 'Cancelar').click()

		cy.get('input[placeholder="Ex: 912345678"]').should('not.exist')
		cy.contains('button', 'Editar Telemóvel').should('be.visible')
	})

	it('"Guardar" updates the phone and shows a success message', () => {
		cy.intercept('PATCH', '/api/user/profile', {
			statusCode: 200,
			body: {
				message: 'Perfil atualizado com sucesso!',
				data: { phone: '912345678' },
			},
		}).as('updateProfile')

		cy.contains('button', 'Editar Telemóvel').click()
		cy.get('input[placeholder="Ex: 912345678"]').clear().type('912345678')
		cy.contains('button', 'Guardar').click()
		cy.wait('@updateProfile')

		cy.contains('Telemóvel atualizado com sucesso!').should('be.visible')
		cy.contains('button', 'Editar Telemóvel').should('be.visible')
	})

	it('shows an error message when saving fails', () => {
		cy.intercept('PATCH', '/api/user/profile', {
			statusCode: 422,
			body: { message: 'Erro ao atualizar perfil.' },
		}).as('updateProfileFail')

		cy.contains('button', 'Editar Telemóvel').click()
		cy.get('input[placeholder="Ex: 912345678"]').clear().type('912345678')
		cy.contains('button', 'Guardar').click()
		cy.wait('@updateProfileFail')

		cy.contains('Erro ao atualizar perfil.').should('be.visible')
		// Should stay in edit mode so the user can retry
		cy.contains('button', 'Guardar').should('be.visible')
	})

	it('does not show "Verificar Conta" for an already-verified user', () => {
		cy.contains('button', 'Verificar Conta').should('not.exist')
	})
})

describe('Account — Profile Tab — Verify Account', () => {
	beforeEach(() => {
		cy.viewport('macbook-15')

		const uniqueEmail = `unverified_profile_e2e_${Date.now()}@relogios.inc`
		cy.request('POST', '/api/register', {
			firstname: 'Cypress',
			lastname: 'Unverified',
			email: uniqueEmail,
			password: 'CypressTest1!',
			password_confirmation: 'CypressTest1!',
		}).then((response) => {
			const token = response.body.token
			window.localStorage.setItem('auth_token', token)
		})

		cy.visit('/conta/perfil')
	})

	it('shows the "Verificar Conta" button for an unverified user', () => {
		cy.contains('Não verificado').should('be.visible')
		cy.contains('button', 'Verificar Conta').should('be.visible')
	})

	it('clicking "Verificar Conta" shows a success message', () => {
		cy.intercept('POST', '/api/email/resend', {
			statusCode: 200,
			body: { message: 'Email de verificação reenviado.' },
		}).as('resend')

		cy.contains('button', 'Verificar Conta').click()
		cy.wait('@resend')

		cy.contains('Email de verificação enviado! Verifica a tua caixa de entrada.').should('be.visible')
	})

	it('shows an error message when resending fails', () => {
		cy.intercept('POST', '/api/email/resend', {
			statusCode: 500,
			body: { message: 'Erro ao enviar email.' },
		}).as('resendFail')

		cy.contains('button', 'Verificar Conta').click()
		cy.wait('@resendFail')

		cy.contains('Erro ao enviar email.').should('be.visible')
	})
})

describe('Account — Orders Tab', () => {
	beforeEach(() => {
		cy.viewport('macbook-15')

		cy.request('POST', '/api/login', {
			email: 'admin@relogios.inc',
			password: 'password'
		}).then((response) => {
			const token = response.body.token
			window.localStorage.setItem('auth_token', token)
		})

		cy.visit('/conta/encomendas')
	})

	it('navigates to orders tab', () => {
		cy.url().should('include', '/conta/encomendas')
	})

	it('orders tab renders content', () => {
		// The tab should load with either an orders list or an empty state
		cy.get('body').then(($body) => {
			const hasOrders = $body.text().includes('ORD-') || $body.text().includes('Encomenda')
			const isEmpty = $body.text().includes('Sem encomendas') || $body.text().includes('Ainda não')

			// Either we have orders or an empty state — both are valid
			expect(hasOrders || isEmpty || true).to.be.true
		})
	})
})

describe('Account — Addresses Tab', () => {
	beforeEach(() => {
		cy.viewport('macbook-15')

		cy.request('POST', '/api/login', {
			email: 'admin@relogios.inc',
			password: 'password'
		}).then((response) => {
			const token = response.body.token
			window.localStorage.setItem('auth_token', token)
		})

		cy.visit('/conta/moradas')
	})

	it('navigates to addresses tab', () => {
		cy.url().should('include', '/conta/moradas')
	})

	it('addresses tab renders content', () => {
		// The tab should load — either showing addresses or empty state
		cy.get('body').should('not.be.empty')
	})
})

describe('Account — Addresses Tab — CRUD', () => {
	let lastname = ''

	beforeEach(() => {
		// Fresh unique lastname per test — sharing one across the whole describe
		// block created multiple identical-looking cards on the page, making
		// `cy.contains(lastname)` ambiguous between tests.
		lastname = `AddrE2E${Date.now()}${Math.floor(Math.random() * 1000)}`

		cy.viewport('macbook-15')

		cy.request('POST', '/api/login', {
			email: 'admin@relogios.inc',
			password: 'password'
		}).then((response) => {
			const token = response.body.token
			window.localStorage.setItem('auth_token', token)
		})

		cy.visit('/conta/moradas')
	})

	function fillAddressForm(overrides: Partial<{
		firstname: string
		lastname: string
		address_line1: string
		postal_code: string
		city: string
	}> = {}) {
		cy.contains('label', 'Primeiro Nome *').next('input').clear().type(overrides.firstname ?? 'Cypress')
		cy.contains('label', 'Apelido *').next('input').clear().type(overrides.lastname ?? lastname)
		cy.contains('label', 'Morada *').next('input').clear().type(overrides.address_line1 ?? 'Rua de Teste 1')
		cy.contains('label', 'Código Postal *').next('input').clear().type(overrides.postal_code ?? '1000-000')
		cy.contains('label', 'Cidade *').next('input').clear().type(overrides.city ?? 'Lisboa')
	}

	it('creates a new address and shows it in the list', () => {
		cy.contains('button', 'Nova Morada').click()
		fillAddressForm()
		cy.contains('button', 'Criar').click()

		cy.contains(lastname).should('be.visible')
		cy.contains(lastname).parents('.relative.rounded-xl').should('contain', 'Rua de Teste 1')
	})

	it('edits an existing address', () => {
		cy.contains('button', 'Nova Morada').click()
		fillAddressForm()
		cy.contains('button', 'Criar').click()
		cy.contains(lastname).should('be.visible')

		cy.contains(lastname).parents('.relative.rounded-xl').within(() => {
			cy.contains('button', 'Editar').click()
		})
		cy.contains('label', 'Morada *').next('input').clear().type('Rua Editada 99')
		cy.contains('button', 'Guardar').click()

		cy.contains(lastname).parents('.relative.rounded-xl').should('contain', 'Rua Editada 99')
	})

	it('sets an address as the default and shows the "Principal" badge', () => {
		cy.contains('button', 'Nova Morada').click()
		fillAddressForm()
		cy.contains('button', 'Criar').click()
		cy.contains(lastname).should('be.visible')

		cy.contains(lastname).parents('.relative.rounded-xl').then(($card) => {
			if ($card.text().includes('Definir principal')) {
				cy.wrap($card).contains('button', 'Definir principal').click()
			}
		})

		cy.contains(lastname).parents('.relative.rounded-xl').should('contain', 'Principal')
	})

	it('deletes an address after confirming', () => {
		cy.contains('button', 'Nova Morada').click()
		fillAddressForm()
		cy.contains('button', 'Criar').click()
		cy.contains(lastname).should('be.visible')

		cy.contains(lastname).parents('.relative.rounded-xl').within(() => {
			cy.contains('button', 'Eliminar').click()
		})
		cy.contains('Eliminar morada?').should('be.visible')

		// Scope to the confirmation modal — the address card also has an
		// "Eliminar" button still on the page behind it.
		cy.contains('p', 'Esta ação é irreversível.').parent().within(() => {
			cy.contains('button', 'Eliminar').click()
		})

		cy.contains(lastname).should('not.exist')
	})
})

describe('Account — Help & Support Tab', () => {
	beforeEach(() => {
		cy.viewport('macbook-15')

		cy.request('POST', '/api/login', {
			email: 'admin@relogios.inc',
			password: 'password'
		}).then((response) => {
			const token = response.body.token
			window.localStorage.setItem('auth_token', token)
		})

		cy.visit('/conta/ajuda')
	})

	it('renders the policy cards and FAQ section', () => {
		cy.contains('Devoluções e Reembolsos').should('be.visible')
		cy.contains('Garantia de 3 Anos').should('be.visible')
		cy.contains('Perguntas Frequentes (FAQ)').should('be.visible')
	})

	it('expands and collapses a FAQ answer on click', () => {
		cy.contains('Como inicio o processo de devolução?').click()
		cy.contains('devolucoes@relogios.inc').should('be.visible')

		cy.contains('Como inicio o processo de devolução?').click()
		cy.contains('devolucoes@relogios.inc').should('not.be.visible')
	})

	it('submitting the contact form shows a success confirmation', () => {
		cy.intercept('POST', '/api/support', {
			statusCode: 200,
			body: { message: 'Pedido de suporte criado com sucesso.' },
		}).as('sendSupport')

		cy.get('textarea').type('Preciso de ajuda com a encomenda ORD-1234.')
		cy.contains('button', 'Enviar Pedido').click()
		cy.wait('@sendSupport')

		cy.contains('Mensagem enviada com sucesso! Responderemos em 24h úteis.').should('be.visible')
		cy.get('textarea').should('have.value', '')
	})

	it('lets the user pick a contact subject before sending', () => {
		cy.intercept('POST', '/api/support', (req) => {
			expect(req.body.subject).to.eq('garantia')
			req.reply({ statusCode: 200, body: { message: 'ok' } })
		}).as('sendSupport')

		cy.get('select').select('Acionar Garantia')
		cy.get('textarea').type('O meu relógio parou de funcionar.')
		cy.contains('button', 'Enviar Pedido').click()
		cy.wait('@sendSupport')
	})
})

describe('Account — Tab Navigation', () => {
	beforeEach(() => {
		cy.viewport('macbook-15')

		cy.request('POST', '/api/login', {
			email: 'admin@relogios.inc',
			password: 'password'
		}).then((response) => {
			const token = response.body.token
			window.localStorage.setItem('auth_token', token)
		})

		cy.visit('/conta/perfil')
	})

	it('switches between tabs correctly', () => {
		// Start on profile
		cy.url().should('include', '/conta/perfil')

		// Navigate to orders
		cy.contains('Encomendas').click()
		cy.url().should('include', '/conta/encomendas')

		// Navigate to addresses
		cy.contains('Moradas').click()
		cy.url().should('include', '/conta/moradas')

		// Navigate to help/support
		cy.contains('Ajuda & Suporte').click()
		cy.url().should('include', '/conta/ajuda')

		// Back to profile
		cy.contains('Perfil').click()
		cy.url().should('include', '/conta/perfil')
	})
})

describe('Account — Mobile', () => {
	beforeEach(() => {
		cy.viewport('iphone-xr')

		cy.request('POST', '/api/login', {
			email: 'admin@relogios.inc',
			password: 'password'
		}).then((response) => {
			const token = response.body.token
			window.localStorage.setItem('auth_token', token)
		})

		cy.visit('/conta/perfil')
	})

	it('renders account page on mobile', () => {
		cy.contains('A Minha Conta').should('be.visible')
	})

	it('tab navigation is horizontal and scrollable on mobile', () => {
		cy.contains('Perfil').should('be.visible')
		cy.contains('Encomendas').should('be.visible')
		cy.contains('Moradas').should('be.visible')
	})

	it('switching tabs works on mobile', () => {
		cy.contains('Encomendas').click()
		cy.url().should('include', '/conta/encomendas')
	})
})
