/**
 * Custom Cypress Commands for Journal Testing
 * Contains reusable commands for journal module testing
 */

// Import Cypress types for better IntelliSense
/// <reference types="cypress" />

// Login command
Cypress.Commands.add('loginAs', (email, password = 'password') => {
  cy.session([email, password], () => {
    cy.visit('/admin/login');
    cy.get('[data-testid="email"]').type(email);
    cy.get('[data-testid="password"]').type(password);
    cy.get('[data-testid="login-button"]').click();

    // Wait for successful login
    cy.url().should('include', '/admin');
    cy.get('[data-testid="user-menu"]').should('be.visible');
  });
});

// Create test journal command
Cypress.Commands.add('createTestJournal', (options = {}) => {
  const defaultOptions = {
    journal_number: null, // Will be auto-generated
    transaction_date: '2024-01-15',
    reference_number: `REF-${Date.now()}`,
    source_type: 'Sale',
    description: 'Test journal created by Cypress',
    status: 'Draft',
    entries: [
      {
        account: 'Kas',
        description: 'Test debit entry',
        debit: 1000000,
        credit: 0,
        sort_order: 1
      },
      {
        account: 'Penjualan',
        description: 'Test credit entry',
        debit: 0,
        credit: 1000000,
        sort_order: 2
      }
    ]
  };

  const journalData = { ...defaultOptions, ...options };

  // Create journal via API for faster test setup
  cy.request({
    method: 'POST',
    url: '/api/test/journals',
    body: journalData,
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json'
    }
  }).then((response) => {
    expect(response.status).to.eq(201);
    return response.body.data;
  });
});

// Create multiple test journals
Cypress.Commands.add('createTestJournals', (count = 5) => {
  const journals = [];

  for (let i = 1; i <= count; i++) {
    const journalData = {
      reference_number: `TEST-${i.toString().padStart(3, '0')}`,
      description: `Test journal ${i} for table operations`,
      status: i % 2 === 0 ? 'Posted' : 'Draft',
      source_type: ['Sale', 'Purchase', 'Payment', 'Receipt'][i % 4]
    };

    journals.push(journalData);
  }

  // Create all journals via batch API
  cy.request({
    method: 'POST',
    url: '/api/test/journals/batch',
    body: { journals },
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json'
    }
  }).then((response) => {
    expect(response.status).to.eq(201);
    return response.body.data;
  });
});

// Clear all test journals
Cypress.Commands.add('clearAllJournals', () => {
  cy.request({
    method: 'DELETE',
    url: '/api/test/journals/clear',
    headers: {
      'Accept': 'application/json'
    }
  }).then((response) => {
    expect(response.status).to.eq(200);
  });
});

// Create test accounts for journal entries
Cypress.Commands.add('createTestAccounts', () => {
  const accounts = [
    {
      kode_akun: '1101',
      nama_akun: 'Kas',
      kategori_akun: 'Aset',
      tipe_akun: 'Debit',
      saldo_awal: 0
    },
    {
      kode_akun: '4101',
      nama_akun: 'Penjualan',
      kategori_akun: 'Pendapatan',
      tipe_akun: 'Kredit',
      saldo_awal: 0
    },
    {
      kode_akun: '1201',
      nama_akun: 'Piutang',
      kategori_akun: 'Aset',
      tipe_akun: 'Debit',
      saldo_awal: 0
    },
    {
      kode_akun: '5101',
      nama_akun: 'Beban Operasional',
      kategori_akun: 'Beban',
      tipe_akun: 'Debit',
      saldo_awal: 0
    }
  ];

  cy.request({
    method: 'POST',
    url: '/api/test/accounts/batch',
    body: { accounts },
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json'
    }
  }).then((response) => {
    expect(response.status).to.eq(201);
    return response.body.data;
  });
});

// Wait for table to load with data
Cypress.Commands.add('waitForTableLoad', (selector = '[data-testid="journals-table"]') => {
  cy.get(selector).should('be.visible');
  cy.get(`${selector} tbody tr`).should('have.length.greaterThan', 0);
  cy.get('[data-testid="table-loading"]').should('not.exist');
});

// Check journal balance
Cypress.Commands.add('verifyJournalBalance', (journalId) => {
  cy.request(`/api/journals/${journalId}/balance`).then((response) => {
    expect(response.body.is_balanced).to.be.true;
    expect(response.body.total_debit).to.equal(response.body.total_credit);
  });
});

// Fill journal form with valid data
Cypress.Commands.add('fillJournalForm', (data = {}) => {
  const defaultData = {
    transaction_date: '2024-01-15',
    reference_number: `REF-${Date.now()}`,
    source_type: 'Sale',
    description: 'Test journal from Cypress',
    status: 'Draft',
    entries: [
      {
        account: 'Kas',
        description: 'Test debit entry',
        debit: '1000000',
        credit: '0'
      },
      {
        account: 'Penjualan',
        description: 'Test credit entry',
        debit: '0',
        credit: '1000000'
      }
    ]
  };

  const formData = { ...defaultData, ...data };

  // Fill basic journal fields
  if (formData.transaction_date) {
    cy.get('[data-testid="transaction-date"]').clear().type(formData.transaction_date);
  }

  if (formData.reference_number) {
    cy.get('[data-testid="reference-number"]').clear().type(formData.reference_number);
  }

  if (formData.source_type) {
    cy.get('[data-testid="source-type"]').select(formData.source_type);
  }

  if (formData.description) {
    cy.get('[data-testid="description"]').clear().type(formData.description);
  }

  if (formData.status) {
    cy.get('[data-testid="status"]').select(formData.status);
  }

  // Fill journal entries
  if (formData.entries && formData.entries.length > 0) {
    formData.entries.forEach((entry, index) => {
      if (entry.account) {
        cy.get(`[data-testid="account-select-${index}"]`).select(entry.account);
      }

      if (entry.description) {
        cy.get(`[data-testid="description-${index}"]`).clear().type(entry.description);
      }

      if (entry.debit) {
        cy.get(`[data-testid="debit-${index}"]`).clear().type(entry.debit);
      }

      if (entry.credit) {
        cy.get(`[data-testid="credit-${index}"]`).clear().type(entry.credit);
      }
    });
  }
});

// Verify notification message
Cypress.Commands.add('verifyNotification', (type, message) => {
  cy.get(`[data-testid="${type}-notification"]`)
    .should('be.visible')
    .and('contain', message);
});

// Check if element is in viewport
Cypress.Commands.add('isInViewport', { prevSubject: true }, (subject) => {
  const bottom = Cypress.$(cy.state('window')).height();
  const rect = subject[0].getBoundingClientRect();

  expect(rect.top).to.be.lessThan(bottom);
  expect(rect.bottom).to.be.greaterThan(0);

  return subject;
});

// Scroll to element if not in viewport (custom implementation)
Cypress.Commands.add('scrollToElement', { prevSubject: true }, (subject) => {
  cy.wrap(subject).then(($el) => {
    $el[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
  });

  return cy.wrap(subject);
});

// Wait for API response
Cypress.Commands.add('waitForAPI', (alias, timeout = 10000) => {
  cy.wait(alias, { timeout });
});

// Check accessibility (wrapper for cypress-axe)
Cypress.Commands.add('checkAccessibility', (selector = null) => {
  if (selector) {
    cy.get(selector).should('be.visible');
    cy.checkA11y(selector);
  } else {
    cy.checkA11y();
  }
});

// Custom assertion for table row count
Cypress.Commands.add('shouldHaveRowCount', { prevSubject: true }, (subject, count) => {
  cy.wrap(subject).find('tbody tr').should('have.length', count);
  return cy.wrap(subject);
});

// Custom assertion for form validation
Cypress.Commands.add('shouldHaveValidationError', { prevSubject: true }, (subject, message) => {
  cy.wrap(subject)
    .parent()
    .find('[data-testid*="error"]')
    .should('be.visible')
    .and('contain', message);

  return cy.wrap(subject);
});

// Setup test environment
Cypress.Commands.add('setupTestEnvironment', () => {
  // Clear existing test data
  cy.clearAllJournals();

  // Create test accounts
  cy.createTestAccounts();

  // Set up any other required test data
  cy.log('Test environment setup completed');
});
