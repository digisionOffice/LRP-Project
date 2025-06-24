/**
 * Journal Form Validation Test Suite
 * Tests for form validation rules and business logic
 */

/// <reference types="cypress" />

describe('Journal Form Validation', () => {
  beforeEach(() => {
    // Login as admin user
    cy.loginAs('admin@example.com');

    // Navigate to create journal page
    cy.visit('/admin/journals/create');

    // Wait for form to load
    cy.get('[data-testid="create-journal-form"]', { timeout: 10000 }).should('be.visible');
  });

  describe('Required Field Validation', () => {
    it('should show validation errors for required fields', () => {
      // Try to submit empty form
      cy.get('[data-testid="submit-journal"]').click();

      // Verify required field errors
      cy.get('[data-testid="transaction-date-error"]')
        .should('be.visible')
        .and('contain', 'Tanggal transaksi wajib diisi');

      cy.get('[data-testid="description-error"]')
        .should('be.visible')
        .and('contain', 'Deskripsi wajib diisi');

      // Check journal entries validation
      cy.get('[data-testid="account-error-0"]')
        .should('be.visible')
        .and('contain', 'Akun wajib dipilih');

      cy.get('[data-testid="entry-description-error-0"]')
        .should('be.visible')
        .and('contain', 'Deskripsi entri wajib diisi');
    });

    it('should clear validation errors when fields are filled', () => {
      // Submit empty form to trigger validation
      cy.get('[data-testid="submit-journal"]').click();

      // Fill required fields one by one and verify errors disappear
      cy.get('[data-testid="transaction-date"]').type('2024-01-15');
      cy.get('[data-testid="transaction-date-error"]').should('not.exist');

      cy.get('[data-testid="description"]').type('Test description');
      cy.get('[data-testid="description-error"]').should('not.exist');

      // Fill journal entry fields
      cy.get('[data-testid="account-select-0"]').select('Kas');
      cy.get('[data-testid="account-error-0"]').should('not.exist');

      cy.get('[data-testid="description-0"]').type('Test entry description');
      cy.get('[data-testid="entry-description-error-0"]').should('not.exist');
    });

    it('should validate minimum journal entries requirement', () => {
      // Fill basic form data
      cy.get('[data-testid="transaction-date"]').type('2024-01-15');
      cy.get('[data-testid="description"]').type('Test with insufficient entries');

      // Remove one of the default entries
      cy.get('[data-testid="remove-entry-1"]').click();

      // Try to submit with only one entry
      cy.get('[data-testid="submit-journal"]').click();

      // Verify minimum entries error
      cy.get('[data-testid="min-entries-error"]')
        .should('be.visible')
        .and('contain', 'Minimal 2 entri jurnal diperlukan');
    });
  });

  describe('Data Format Validation', () => {
    it('should validate date format', () => {
      // Test invalid date format
      cy.get('[data-testid="transaction-date"]').type('invalid-date');
      cy.get('[data-testid="description"]').type('Test description');
      cy.get('[data-testid="submit-journal"]').click();

      cy.get('[data-testid="transaction-date-error"]')
        .should('be.visible')
        .and('contain', 'Format tanggal tidak valid');
    });

    it('should validate numeric fields for debit/credit', () => {
      // Fill basic form
      cy.get('[data-testid="transaction-date"]').type('2024-01-15');
      cy.get('[data-testid="description"]').type('Test numeric validation');

      // Fill first entry with invalid numeric values
      cy.get('[data-testid="account-select-0"]').select('Kas');
      cy.get('[data-testid="description-0"]').type('Test entry');
      cy.get('[data-testid="debit-0"]').type('invalid-number');

      cy.get('[data-testid="submit-journal"]').click();

      cy.get('[data-testid="debit-error-0"]')
        .should('be.visible')
        .and('contain', 'Nilai debit harus berupa angka');
    });

    it('should validate maximum length for text fields', () => {
      const longText = 'a'.repeat(300); // Assuming max length is 255

      cy.get('[data-testid="reference-number"]').type(longText);
      cy.get('[data-testid="submit-journal"]').click();

      cy.get('[data-testid="reference-number-error"]')
        .should('be.visible')
        .and('contain', 'Nomor referensi maksimal 255 karakter');
    });
  });

  describe('Business Logic Validation', () => {
    it('should validate journal balance (debit = credit)', () => {
      // Fill basic form
      cy.get('[data-testid="transaction-date"]').type('2024-01-15');
      cy.get('[data-testid="description"]').type('Test unbalanced journal');

      // Create unbalanced entries
      cy.get('[data-testid="account-select-0"]').select('Kas');
      cy.get('[data-testid="description-0"]').type('Cash entry');
      cy.get('[data-testid="debit-0"]').type('1000000');

      cy.get('[data-testid="account-select-1"]').select('Penjualan');
      cy.get('[data-testid="description-1"]').type('Sales entry');
      cy.get('[data-testid="credit-1"]').type('500000'); // Unbalanced

      cy.get('[data-testid="submit-journal"]').click();

      // Verify balance validation error
      cy.get('[data-testid="balance-error"]')
        .should('be.visible')
        .and('contain', 'Total debit harus sama dengan total kredit');

      // Show current totals in error message
      cy.get('[data-testid="balance-error"]')
        .should('contain', 'Debit: Rp 1,000,000')
        .and('contain', 'Kredit: Rp 500,000');
    });

    it('should prevent both debit and credit being zero', () => {
      // Fill basic form
      cy.get('[data-testid="transaction-date"]').type('2024-01-15');
      cy.get('[data-testid="description"]').type('Test zero amounts');

      // Create entry with both debit and credit as zero
      cy.get('[data-testid="account-select-0"]').select('Kas');
      cy.get('[data-testid="description-0"]').type('Zero entry');
      cy.get('[data-testid="debit-0"]').clear().type('0');
      cy.get('[data-testid="credit-0"]').clear().type('0');

      cy.get('[data-testid="submit-journal"]').click();

      cy.get('[data-testid="zero-amount-error-0"]')
        .should('be.visible')
        .and('contain', 'Debit atau kredit harus memiliki nilai');
    });

    it('should prevent both debit and credit being filled', () => {
      // Fill basic form
      cy.get('[data-testid="transaction-date"]').type('2024-01-15');
      cy.get('[data-testid="description"]').type('Test double entry');

      // Create entry with both debit and credit filled
      cy.get('[data-testid="account-select-0"]').select('Kas');
      cy.get('[data-testid="description-0"]').type('Double entry');
      cy.get('[data-testid="debit-0"]').type('1000000');
      cy.get('[data-testid="credit-0"]').type('500000');

      cy.get('[data-testid="submit-journal"]').click();

      cy.get('[data-testid="double-entry-error-0"]')
        .should('be.visible')
        .and('contain', 'Tidak boleh mengisi debit dan kredit bersamaan');
    });

    it('should validate negative amounts', () => {
      // Fill basic form
      cy.get('[data-testid="transaction-date"]').type('2024-01-15');
      cy.get('[data-testid="description"]').type('Test negative amounts');

      // Enter negative debit amount
      cy.get('[data-testid="account-select-0"]').select('Kas');
      cy.get('[data-testid="description-0"]').type('Negative entry');
      cy.get('[data-testid="debit-0"]').type('-1000000');

      cy.get('[data-testid="submit-journal"]').click();

      cy.get('[data-testid="negative-amount-error-0"]')
        .should('be.visible')
        .and('contain', 'Nilai debit tidak boleh negatif');
    });

    it('should show real-time balance calculation', () => {
      // Fill basic form
      cy.get('[data-testid="transaction-date"]').type('2024-01-15');
      cy.get('[data-testid="description"]').type('Test balance calculation');

      // Add first entry
      cy.get('[data-testid="account-select-0"]').select('Kas');
      cy.get('[data-testid="description-0"]').type('Cash entry');
      cy.get('[data-testid="debit-0"]').type('1000000');

      // Verify total debit updates
      cy.get('[data-testid="total-debit-display"]').should('contain', 'Rp 1,000,000');
      cy.get('[data-testid="total-credit-display"]').should('contain', 'Rp 0');
      cy.get('[data-testid="balance-difference"]').should('contain', 'Rp 1,000,000');

      // Add second entry
      cy.get('[data-testid="account-select-1"]').select('Penjualan');
      cy.get('[data-testid="description-1"]').type('Sales entry');
      cy.get('[data-testid="credit-1"]').type('1000000');

      // Verify balance is now equal
      cy.get('[data-testid="total-credit-display"]').should('contain', 'Rp 1,000,000');
      cy.get('[data-testid="balance-difference"]').should('contain', 'Rp 0');
      cy.get('[data-testid="balance-status"]').should('contain', 'Seimbang');
    });
  });

  describe('Account Selection Validation', () => {
    it('should prevent selecting the same account multiple times', () => {
      // Fill basic form
      cy.get('[data-testid="transaction-date"]').type('2024-01-15');
      cy.get('[data-testid="description"]').type('Test duplicate accounts');

      // Select same account for both entries
      cy.get('[data-testid="account-select-0"]').select('Kas');
      cy.get('[data-testid="account-select-1"]').select('Kas');

      cy.get('[data-testid="submit-journal"]').click();

      cy.get('[data-testid="duplicate-account-error"]')
        .should('be.visible')
        .and('contain', 'Akun yang sama tidak boleh digunakan lebih dari sekali');
    });

    it('should validate account exists and is active', () => {
      // This test assumes there's validation for inactive accounts
      cy.get('[data-testid="transaction-date"]').type('2024-01-15');
      cy.get('[data-testid="description"]').type('Test inactive account');

      // Try to select an inactive account (if any exist in test data)
      cy.get('[data-testid="account-select-0"]').select('Inactive Account');
      cy.get('[data-testid="submit-journal"]').click();

      cy.get('[data-testid="inactive-account-error-0"]')
        .should('be.visible')
        .and('contain', 'Akun yang dipilih tidak aktif');
    });
  });

  describe('Form State Management', () => {
    it('should preserve form data when validation fails', () => {
      // Fill form with some data
      cy.get('[data-testid="transaction-date"]').type('2024-01-15');
      cy.get('[data-testid="reference-number"]').type('REF-PRESERVE-001');
      cy.get('[data-testid="description"]').type('Test data preservation');
      cy.get('[data-testid="account-select-0"]').select('Kas');
      cy.get('[data-testid="description-0"]').type('Test entry');
      cy.get('[data-testid="debit-0"]').type('1000000');

      // Submit with unbalanced entries (will fail validation)
      cy.get('[data-testid="submit-journal"]').click();

      // Verify form data is preserved
      cy.get('[data-testid="transaction-date"]').should('have.value', '2024-01-15');
      cy.get('[data-testid="reference-number"]').should('have.value', 'REF-PRESERVE-001');
      cy.get('[data-testid="description"]').should('have.value', 'Test data preservation');
      cy.get('[data-testid="account-select-0"]').should('have.value', 'kas-account-id');
      cy.get('[data-testid="debit-0"]').should('have.value', '1000000');
    });

    it('should reset form when reset button is clicked', () => {
      // Fill form with data
      cy.get('[data-testid="transaction-date"]').type('2024-01-15');
      cy.get('[data-testid="reference-number"]').type('REF-RESET-001');
      cy.get('[data-testid="description"]').type('Test reset functionality');

      // Click reset button
      cy.get('[data-testid="reset-form"]').click();

      // Verify form is cleared
      cy.get('[data-testid="transaction-date"]').should('have.value', '');
      cy.get('[data-testid="reference-number"]').should('have.value', '');
      cy.get('[data-testid="description"]').should('have.value', '');
    });

    it('should show loading state during form submission', () => {
      // Fill valid form
      cy.get('[data-testid="transaction-date"]').type('2024-01-15');
      cy.get('[data-testid="description"]').type('Test loading state');

      cy.get('[data-testid="account-select-0"]').select('Kas');
      cy.get('[data-testid="description-0"]').type('Cash entry');
      cy.get('[data-testid="debit-0"]').type('1000000');

      cy.get('[data-testid="account-select-1"]').select('Penjualan');
      cy.get('[data-testid="description-1"]').type('Sales entry');
      cy.get('[data-testid="credit-1"]').type('1000000');

      // Intercept form submission to simulate slow response
      cy.intercept('POST', '/admin/journals', { delay: 2000 }).as('createJournal');

      // Submit form
      cy.get('[data-testid="submit-journal"]').click();

      // Verify loading state
      cy.get('[data-testid="submit-journal"]')
        .should('be.disabled')
        .and('contain', 'Menyimpan...');

      cy.get('[data-testid="loading-spinner"]').should('be.visible');

      // Wait for submission to complete
      cy.wait('@createJournal');
    });
  });
});
