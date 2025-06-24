/**
 * Journal Table & UX Test Suite
 * Tests for table interactions, search, filters, sorting, and user experience
 */

/// <reference types="cypress" />

describe('Journal Table & UX Features', () => {
  beforeEach(() => {
    // Login as admin user
    cy.loginAs('admin@example.com');

    // Create test data for table operations
    cy.createTestJournals();

    // Navigate to journals list
    cy.visit('/admin/journals');

    // Wait for table to load
    cy.get('[data-testid="journals-table"]', { timeout: 10000 }).should('be.visible');
  });

  describe('Search Functionality', () => {
    it('should search journals by journal number', () => {
      // Create specific journal for search test
      cy.createTestJournal({
        journal_number: 'JRN-202401-9999',
        reference_number: 'SEARCH-TEST-001'
      });

      // Perform search by journal number
      cy.get('[data-testid="search-input"]').type('JRN-202401-9999');
      cy.get('[data-testid="search-button"]').click();

      // Verify search results
      cy.get('[data-testid="journals-table"] tbody tr').should('have.length', 1);
      cy.get('[data-testid="journals-table"]').should('contain', 'JRN-202401-9999');

      // Verify other journals are not shown
      cy.get('[data-testid="journals-table"]').should('not.contain', 'SEARCH-TEST-002');
    });

    it('should search journals by reference number', () => {
      // Perform search by reference number
      cy.get('[data-testid="search-input"]').type('REF-SEARCH-001');
      cy.get('[data-testid="search-button"]').click();

      // Verify search results
      cy.get('[data-testid="journals-table"]')
        .should('contain', 'REF-SEARCH-001')
        .and('not.contain', 'REF-SEARCH-002');
    });

    it('should handle empty search results', () => {
      // Search for non-existent journal
      cy.get('[data-testid="search-input"]').type('NON-EXISTENT-JOURNAL');
      cy.get('[data-testid="search-button"]').click();

      // Verify empty state
      cy.get('[data-testid="empty-state"]')
        .should('be.visible')
        .and('contain', 'Tidak ada jurnal yang ditemukan');

      cy.get('[data-testid="journals-table"] tbody tr').should('have.length', 0);
    });

    it('should clear search and show all results', () => {
      // Perform search first
      cy.get('[data-testid="search-input"]').type('SPECIFIC-SEARCH');
      cy.get('[data-testid="search-button"]').click();

      // Clear search
      cy.get('[data-testid="clear-search"]').click();

      // Verify all journals are shown again
      cy.get('[data-testid="search-input"]').should('have.value', '');
      cy.get('[data-testid="journals-table"] tbody tr').should('have.length.greaterThan', 1);
    });

    it('should perform real-time search as user types', () => {
      // Enable real-time search if available
      cy.get('[data-testid="search-input"]').type('REF-');

      // Wait for debounced search
      cy.wait(500);

      // Verify filtered results
      cy.get('[data-testid="journals-table"] tbody tr').each(($row) => {
        cy.wrap($row).should('contain', 'REF-');
      });
    });
  });

  describe('Filter Functionality', () => {
    beforeEach(() => {
      // Create journals with different statuses and source types
      const testData = [
        { reference_number: 'FILTER-DRAFT-001', status: 'Draft', source_type: 'Sale' },
        { reference_number: 'FILTER-POSTED-001', status: 'Posted', source_type: 'Purchase' },
        { reference_number: 'FILTER-CANCELLED-001', status: 'Cancelled', source_type: 'Payment' },
        { reference_number: 'FILTER-ERROR-001', status: 'Error', source_type: 'Receipt' }
      ];

      testData.forEach(data => {
        cy.createTestJournal(data);
      });
    });

    it('should filter journals by status', () => {
      // Test each status filter
      const statuses = ['Draft', 'Posted', 'Cancelled', 'Error'];

      statuses.forEach(status => {
        // Apply status filter
        cy.get('[data-testid="status-filter"]').select(status);
        cy.get('[data-testid="apply-filters"]').click();

        // Verify only journals with selected status are shown
        cy.get('[data-testid="journals-table"] tbody tr').each(($row) => {
          cy.wrap($row).find('[data-testid="status-badge"]').should('contain', status);
        });

        // Verify specific test journal appears
        cy.get('[data-testid="journals-table"]').should('contain', `FILTER-${status.toUpperCase()}-001`);
      });
    });

    it('should filter journals by source type', () => {
      const sourceTypes = ['Sale', 'Purchase', 'Payment', 'Receipt', 'ManualAdjust'];

      sourceTypes.forEach(sourceType => {
        // Apply source type filter
        cy.get('[data-testid="source-type-filter"]').select(sourceType);
        cy.get('[data-testid="apply-filters"]').click();

        // Verify only journals with selected source type are shown
        cy.get('[data-testid="journals-table"] tbody tr').each(($row) => {
          cy.wrap($row).find('[data-testid="source-type-badge"]').should('contain', sourceType);
        });
      });
    });

    it('should apply multiple filters simultaneously', () => {
      // Apply both status and source type filters
      cy.get('[data-testid="status-filter"]').select('Posted');
      cy.get('[data-testid="source-type-filter"]').select('Purchase');
      cy.get('[data-testid="apply-filters"]').click();

      // Verify results match both criteria
      cy.get('[data-testid="journals-table"]').should('contain', 'FILTER-POSTED-001');
      cy.get('[data-testid="journals-table"]').should('not.contain', 'FILTER-DRAFT-001');
      cy.get('[data-testid="journals-table"]').should('not.contain', 'FILTER-CANCELLED-001');
    });

    it('should reset all filters', () => {
      // Apply filters first
      cy.get('[data-testid="status-filter"]').select('Draft');
      cy.get('[data-testid="source-type-filter"]').select('Sale');
      cy.get('[data-testid="apply-filters"]').click();

      // Reset filters
      cy.get('[data-testid="reset-filters"]').click();

      // Verify filters are cleared
      cy.get('[data-testid="status-filter"]').should('have.value', '');
      cy.get('[data-testid="source-type-filter"]').should('have.value', '');

      // Verify all journals are shown
      cy.get('[data-testid="journals-table"] tbody tr').should('have.length.greaterThan', 3);
    });

    it('should show filter count and active filter indicators', () => {
      // Apply status filter
      cy.get('[data-testid="status-filter"]').select('Posted');
      cy.get('[data-testid="apply-filters"]').click();

      // Verify active filter indicator
      cy.get('[data-testid="active-filters"]')
        .should('be.visible')
        .and('contain', 'Status: Posted');

      // Verify result count
      cy.get('[data-testid="results-count"]')
        .should('be.visible')
        .and('contain', 'Menampilkan');
    });
  });

  describe('Sorting Functionality', () => {
    it('should sort by journal number', () => {
      // Click journal number column header
      cy.get('[data-testid="sort-journal-number"]').click();

      // Verify ascending sort
      cy.get('[data-testid="sort-journal-number"]')
        .should('have.class', 'sort-asc')
        .find('[data-testid="sort-icon"]')
        .should('be.visible');

      // Verify data is sorted
      cy.get('[data-testid="journals-table"] tbody tr')
        .first()
        .should('contain', 'JRN-202401-0001');

      // Click again for descending sort
      cy.get('[data-testid="sort-journal-number"]').click();

      cy.get('[data-testid="sort-journal-number"]')
        .should('have.class', 'sort-desc');
    });

    it('should sort by transaction date', () => {
      // Click date column header
      cy.get('[data-testid="sort-transaction-date"]').click();

      // Verify sort indicator
      cy.get('[data-testid="sort-transaction-date"]')
        .should('have.class', 'sort-asc');

      // Verify dates are in ascending order
      let previousDate = null;
      cy.get('[data-testid="journals-table"] tbody tr [data-testid="transaction-date"]').each(($el) => {
        const currentDate = new Date($el.text());
        if (previousDate) {
          expect(currentDate.getTime()).to.be.at.least(previousDate.getTime());
        }
        previousDate = currentDate;
      });
    });

    it('should maintain sort when filtering', () => {
      // Apply sort first
      cy.get('[data-testid="sort-journal-number"]').click();

      // Apply filter
      cy.get('[data-testid="status-filter"]').select('Posted');
      cy.get('[data-testid="apply-filters"]').click();

      // Verify sort is maintained
      cy.get('[data-testid="sort-journal-number"]').should('have.class', 'sort-asc');
    });
  });

  describe('Pagination', () => {
    beforeEach(() => {
      // Create enough test data to trigger pagination
      for (let i = 1; i <= 25; i++) {
        cy.createTestJournal({
          reference_number: `PAGINATION-${i.toString().padStart(3, '0')}`,
          description: `Test journal ${i} for pagination`
        });
      }
    });

    it('should navigate between pages', () => {
      // Verify pagination controls are visible
      cy.get('[data-testid="pagination"]').should('be.visible');
      cy.get('[data-testid="page-info"]').should('contain', 'Halaman 1');

      // Go to next page
      cy.get('[data-testid="next-page"]').click();

      // Verify page change
      cy.get('[data-testid="page-info"]').should('contain', 'Halaman 2');
      cy.url().should('include', 'page=2');

      // Go to previous page
      cy.get('[data-testid="prev-page"]').click();

      // Verify back to first page
      cy.get('[data-testid="page-info"]').should('contain', 'Halaman 1');
    });

    it('should change items per page', () => {
      // Change items per page
      cy.get('[data-testid="items-per-page"]').select('50');

      // Verify more items are shown
      cy.get('[data-testid="journals-table"] tbody tr').should('have.length', 25);

      // Verify pagination is updated
      cy.get('[data-testid="page-info"]').should('contain', 'Halaman 1 dari 1');
    });

    it('should show correct page information', () => {
      // Verify page information display
      cy.get('[data-testid="page-info"]')
        .should('contain', 'Menampilkan')
        .and('contain', 'dari')
        .and('contain', 'total');

      // Verify items range
      cy.get('[data-testid="items-range"]')
        .should('contain', '1-10 dari 25');
    });
  });

  describe('Responsive Design', () => {
    it('should adapt to mobile viewport', () => {
      // Set mobile viewport
      cy.viewport(375, 667);

      // Verify mobile-specific elements
      cy.get('[data-testid="mobile-menu-toggle"]').should('be.visible');
      cy.get('[data-testid="desktop-table"]').should('not.be.visible');
      cy.get('[data-testid="mobile-cards"]').should('be.visible');

      // Test mobile card interactions
      cy.get('[data-testid="mobile-card"]').first().click();
      cy.get('[data-testid="mobile-actions"]').should('be.visible');
    });

    it('should adapt to tablet viewport', () => {
      // Set tablet viewport
      cy.viewport(768, 1024);

      // Verify tablet layout
      cy.get('[data-testid="journals-table"]').should('be.visible');
      cy.get('[data-testid="table-actions"]').should('be.visible');

      // Test horizontal scroll if needed
      cy.get('[data-testid="table-container"]').scrollTo('right');
      cy.get('[data-testid="action-column"]').should('be.visible');
    });
  });

  describe('User Experience Features', () => {
    it('should show loading states', () => {
      // Intercept API calls to simulate loading
      cy.intercept('GET', '/admin/journals*', { delay: 1000 }).as('loadJournals');

      // Reload page to trigger loading
      cy.reload();

      // Verify loading state
      cy.get('[data-testid="table-loading"]').should('be.visible');
      cy.get('[data-testid="loading-skeleton"]').should('be.visible');

      // Wait for data to load
      cy.wait('@loadJournals');
      cy.get('[data-testid="table-loading"]').should('not.exist');
    });

    it('should show tooltips for truncated content', () => {
      // Create journal with long description
      cy.createTestJournal({
        reference_number: 'TOOLTIP-TEST',
        description: 'This is a very long description that should be truncated in the table view and show a tooltip when hovered over'
      });

      // Hover over truncated description
      cy.get('[data-testid="journals-table"]')
        .contains('tr', 'TOOLTIP-TEST')
        .find('[data-testid="description-cell"]')
        .trigger('mouseover');

      // Verify tooltip appears
      cy.get('[data-testid="description-tooltip"]')
        .should('be.visible')
        .and('contain', 'This is a very long description');
    });

    it('should provide keyboard navigation', () => {
      // Focus on table
      cy.get('[data-testid="journals-table"]').focus();

      // Navigate with arrow keys
      cy.get('body').type('{downarrow}');
      cy.get('[data-testid="journals-table"] tbody tr:first-child')
        .should('have.class', 'focused');

      // Navigate to actions with tab
      cy.get('body').type('{tab}');
      cy.get('[data-testid="view-action"]').first().should('be.focused');
    });

    it('should show contextual help and empty states', () => {
      // Clear all data to show empty state
      cy.clearAllJournals();

      // Verify empty state
      cy.get('[data-testid="empty-state"]')
        .should('be.visible')
        .and('contain', 'Belum ada jurnal')
        .and('contain', 'Mulai dengan membuat jurnal pertama');

      // Verify help text
      cy.get('[data-testid="help-text"]')
        .should('be.visible')
        .and('contain', 'Jurnal digunakan untuk mencatat transaksi');

      // Verify create button in empty state
      cy.get('[data-testid="empty-state-create-btn"]').should('be.visible');
    });
  });
});
