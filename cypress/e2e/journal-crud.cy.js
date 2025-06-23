/**
 * Journal CRUD Operations Test Suite
 * Tests for Create, Read, Update, Delete operations on Journal module
 */

/// <reference types="cypress" />

describe('Journal CRUD Operations', () => {
  beforeEach(() => {
    // Login as admin user before each test
    cy.loginAs('admin@example.com');

    // Navigate to Journal module
    cy.visit('/admin/journals');

    // Wait for page to load
    cy.get('[data-testid="journals-table"]', { timeout: 10000 }).should('be.visible');
  });

  describe('Create Journal', () => {
    it('should create a new journal with valid data', () => {
      // Click create button
      cy.get('[data-testid="create-journal-btn"]').click();

      // Fill journal form
      cy.get('[data-testid="transaction-date"]').type('2024-01-15');
      cy.get('[data-testid="reference-number"]').type('REF-001');
      cy.get('[data-testid="source-type"]').select('Sale');
      cy.get('[data-testid="description"]').type('Test sales transaction for Cypress testing');
      cy.get('[data-testid="status"]').select('Draft');

      // Add first journal entry (Debit)
      cy.get('[data-testid="journal-entries"] [data-testid="account-select-0"]').select('Kas');
      cy.get('[data-testid="journal-entries"] [data-testid="description-0"]').type('Cash received from sales');
      cy.get('[data-testid="journal-entries"] [data-testid="debit-0"]').type('1000000');
      cy.get('[data-testid="journal-entries"] [data-testid="credit-0"]').should('have.value', '0');

      // Add second journal entry (Credit)
      cy.get('[data-testid="journal-entries"] [data-testid="account-select-1"]').select('Penjualan');
      cy.get('[data-testid="journal-entries"] [data-testid="description-1"]').type('Sales revenue');
      cy.get('[data-testid="journal-entries"] [data-testid="debit-1"]').should('have.value', '0');
      cy.get('[data-testid="journal-entries"] [data-testid="credit-1"]').type('1000000');

      // Submit form
      cy.get('[data-testid="submit-journal"]').click();

      // Verify success message
      cy.get('[data-testid="success-notification"]')
        .should('be.visible')
        .and('contain', 'Journal created successfully');

      // Verify redirect to journal list
      cy.url().should('include', '/admin/journals');

      // Verify new journal appears in table
      cy.get('[data-testid="journals-table"]')
        .should('contain', 'REF-001')
        .and('contain', 'Test sales transaction');
    });

    it('should create journal with different source types', () => {
      const sourceTypes = ['Sale', 'Purchase', 'Payment', 'Receipt', 'ManualAdjust'];

      sourceTypes.forEach((sourceType, index) => {
        cy.get('[data-testid="create-journal-btn"]').click();

        // Fill basic form data
        cy.get('[data-testid="transaction-date"]').type('2024-01-15');
        cy.get('[data-testid="reference-number"]').type(`REF-${sourceType}-${index}`);
        cy.get('[data-testid="source-type"]').select(sourceType);
        cy.get('[data-testid="description"]').type(`Test ${sourceType} transaction`);

        // Add balanced entries
        cy.get('[data-testid="journal-entries"] [data-testid="account-select-0"]').select('Kas');
        cy.get('[data-testid="journal-entries"] [data-testid="description-0"]').type('Test entry 1');
        cy.get('[data-testid="journal-entries"] [data-testid="debit-0"]').type('500000');

        cy.get('[data-testid="journal-entries"] [data-testid="account-select-1"]').select('Penjualan');
        cy.get('[data-testid="journal-entries"] [data-testid="description-1"]').type('Test entry 2');
        cy.get('[data-testid="journal-entries"] [data-testid="credit-1"]').type('500000');

        cy.get('[data-testid="submit-journal"]').click();

        // Verify creation and return to list
        cy.get('[data-testid="success-notification"]').should('be.visible');
        cy.url().should('include', '/admin/journals');

        // Verify source type badge in table
        cy.get('[data-testid="journals-table"]')
          .should('contain', `REF-${sourceType}-${index}`)
          .and('contain', sourceType);
      });
    });

    it('should add and remove journal entries using repeater', () => {
      cy.get('[data-testid="create-journal-btn"]').click();

      // Fill basic form
      cy.get('[data-testid="transaction-date"]').type('2024-01-15');
      cy.get('[data-testid="description"]').type('Multi-entry journal test');

      // Verify default 2 entries exist
      cy.get('[data-testid="journal-entries"] [data-testid="entry-row"]').should('have.length', 2);

      // Add third entry
      cy.get('[data-testid="add-journal-entry"]').click();
      cy.get('[data-testid="journal-entries"] [data-testid="entry-row"]').should('have.length', 3);

      // Fill all three entries
      const entries = [
        { account: 'Kas', description: 'Cash entry', debit: '1000000', credit: '0' },
        { account: 'Piutang', description: 'Receivable entry', debit: '500000', credit: '0' },
        { account: 'Penjualan', description: 'Sales entry', debit: '0', credit: '1500000' }
      ];

      entries.forEach((entry, index) => {
        cy.get(`[data-testid="account-select-${index}"]`).select(entry.account);
        cy.get(`[data-testid="description-${index}"]`).type(entry.description);
        cy.get(`[data-testid="debit-${index}"]`).clear().type(entry.debit);
        cy.get(`[data-testid="credit-${index}"]`).clear().type(entry.credit);
      });

      // Remove middle entry
      cy.get('[data-testid="remove-entry-1"]').click();
      cy.get('[data-testid="journal-entries"] [data-testid="entry-row"]').should('have.length', 2);

      // Verify remaining entries are correct
      cy.get('[data-testid="account-select-0"]').should('contain', 'Kas');
      cy.get('[data-testid="account-select-1"]').should('contain', 'Penjualan');
    });
  });

  describe('Read Journal', () => {
    beforeEach(() => {
      // Create test data
      cy.createTestJournal({
        reference_number: 'READ-TEST-001',
        description: 'Test journal for read operations',
        status: 'Posted'
      });
    });

    it('should display journal list with correct columns', () => {
      // Verify table headers
      const expectedHeaders = ['Nomor Jurnal', 'Tanggal', 'Referensi', 'Tipe', 'Deskripsi', 'Status'];
      expectedHeaders.forEach(header => {
        cy.get('[data-testid="journals-table"] thead').should('contain', header);
      });

      // Verify test journal appears in table
      cy.get('[data-testid="journals-table"] tbody')
        .should('contain', 'READ-TEST-001')
        .and('contain', 'Test journal for read operations');
    });

    it('should view journal details', () => {
      // Find and click view action for test journal
      cy.get('[data-testid="journals-table"]')
        .contains('tr', 'READ-TEST-001')
        .find('[data-testid="view-action"]')
        .click();

      // Verify detail page loads
      cy.url().should('include', '/admin/journals/');
      cy.get('[data-testid="journal-detail"]').should('be.visible');

      // Verify journal information is displayed
      cy.get('[data-testid="journal-reference"]').should('contain', 'READ-TEST-001');
      cy.get('[data-testid="journal-description"]').should('contain', 'Test journal for read operations');
      cy.get('[data-testid="journal-status"]').should('contain', 'Posted');

      // Verify journal entries are displayed
      cy.get('[data-testid="journal-entries-list"]').should('be.visible');
      cy.get('[data-testid="total-debit"]').should('be.visible');
      cy.get('[data-testid="total-credit"]').should('be.visible');
    });

    it('should display status badges with correct colors', () => {
      const statusColors = {
        'Draft': 'secondary',
        'Posted': 'success',
        'Cancelled': 'danger',
        'Error': 'warning'
      };

      Object.entries(statusColors).forEach(([status, color]) => {
        // Create journal with specific status
        cy.createTestJournal({
          reference_number: `STATUS-${status}`,
          status: status
        });

        // Verify badge color
        cy.get('[data-testid="journals-table"]')
          .contains('tr', `STATUS-${status}`)
          .find('[data-testid="status-badge"]')
          .should('have.class', `badge-${color}`);
      });
    });
  });

  describe('Update Journal', () => {
    beforeEach(() => {
      // Create draft journal for editing
      cy.createTestJournal({
        reference_number: 'EDIT-TEST-001',
        description: 'Original description',
        status: 'Draft'
      });
    });

    it('should edit draft journal successfully', () => {
      // Find and click edit action
      cy.get('[data-testid="journals-table"]')
        .contains('tr', 'EDIT-TEST-001')
        .find('[data-testid="edit-action"]')
        .click();

      // Verify edit form loads
      cy.url().should('include', '/edit');
      cy.get('[data-testid="edit-journal-form"]').should('be.visible');

      // Update journal data
      cy.get('[data-testid="reference-number"]').clear().type('EDIT-TEST-001-UPDATED');
      cy.get('[data-testid="description"]').clear().type('Updated description for testing');

      // Update first journal entry
      cy.get('[data-testid="description-0"]').clear().type('Updated entry description');
      cy.get('[data-testid="debit-0"]').clear().type('2000000');

      // Update second journal entry to maintain balance
      cy.get('[data-testid="credit-1"]').clear().type('2000000');

      // Submit changes
      cy.get('[data-testid="update-journal"]').click();

      // Verify success message
      cy.get('[data-testid="success-notification"]')
        .should('be.visible')
        .and('contain', 'Journal updated successfully');

      // Verify changes in table
      cy.visit('/admin/journals');
      cy.get('[data-testid="journals-table"]')
        .should('contain', 'EDIT-TEST-001-UPDATED')
        .and('contain', 'Updated description for testing');
    });

    it('should not allow editing posted journals', () => {
      // Create posted journal
      cy.createTestJournal({
        reference_number: 'POSTED-TEST-001',
        status: 'Posted'
      });

      // Verify edit button is not visible for posted journal
      cy.get('[data-testid="journals-table"]')
        .contains('tr', 'POSTED-TEST-001')
        .find('[data-testid="edit-action"]')
        .should('not.exist');
    });
  });

  describe('Delete Journal', () => {
    beforeEach(() => {
      // Create test journals for deletion
      cy.createTestJournal({
        reference_number: 'DELETE-TEST-001',
        status: 'Draft'
      });
      cy.createTestJournal({
        reference_number: 'DELETE-TEST-002',
        status: 'Posted'
      });
    });

    it('should delete individual draft journal', () => {
      // Find and click delete action for draft journal
      cy.get('[data-testid="journals-table"]')
        .contains('tr', 'DELETE-TEST-001')
        .find('[data-testid="delete-action"]')
        .click();

      // Confirm deletion in modal
      cy.get('[data-testid="confirm-delete-modal"]').should('be.visible');
      cy.get('[data-testid="confirm-delete-btn"]').click();

      // Verify success message
      cy.get('[data-testid="success-notification"]')
        .should('be.visible')
        .and('contain', 'Journal deleted successfully');

      // Verify journal is removed from table
      cy.get('[data-testid="journals-table"]')
        .should('not.contain', 'DELETE-TEST-001');
    });

    it('should perform bulk delete for draft journals only', () => {
      // Create multiple draft journals
      ['BULK-1', 'BULK-2', 'BULK-3'].forEach(ref => {
        cy.createTestJournal({
          reference_number: ref,
          status: 'Draft'
        });
      });

      // Select multiple journals
      cy.get('[data-testid="select-journal-BULK-1"]').check();
      cy.get('[data-testid="select-journal-BULK-2"]').check();
      cy.get('[data-testid="select-journal-DELETE-TEST-002"]').check(); // Posted journal

      // Click bulk delete
      cy.get('[data-testid="bulk-delete-btn"]').click();

      // Confirm bulk deletion
      cy.get('[data-testid="confirm-bulk-delete-modal"]').should('be.visible');
      cy.get('[data-testid="confirm-bulk-delete-btn"]').click();

      // Verify only draft journals are deleted
      cy.get('[data-testid="journals-table"]')
        .should('not.contain', 'BULK-1')
        .and('not.contain', 'BULK-2')
        .and('contain', 'DELETE-TEST-002'); // Posted journal should remain
    });
  });
});
