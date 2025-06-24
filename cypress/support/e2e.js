/**
 * Cypress E2E Support File
 * This file is processed and loaded automatically before test files
 */

// Import Cypress types for better IntelliSense
/// <reference types="cypress" />

// Import commands
import './commands.js';

// Import third-party plugins
import 'cypress-axe'; // For accessibility testing
// Note: cypress-terminal-report will be configured differently for ES modules

// Global configuration
Cypress.on('uncaught:exception', (err, runnable) => {
  // Prevent Cypress from failing on uncaught exceptions
  // that might occur in the application
  console.log('Uncaught exception:', err.message);

  // Return false to prevent the error from failing the test
  // Only for specific known issues
  if (err.message.includes('ResizeObserver loop limit exceeded')) {
    return false;
  }

  if (err.message.includes('Non-Error promise rejection captured')) {
    return false;
  }

  // Let other errors fail the test
  return true;
});

// Global before hook
before(() => {
  // Setup test environment
  cy.log('Setting up test environment...');

  // Inject accessibility testing only if axe is available
  if (Cypress.env('enableAccessibilityTests')) {
    cy.window().then((win) => {
      if (typeof win.axe !== 'undefined') {
        cy.injectAxe();
      }
    });
  }
});

// Global beforeEach hook
beforeEach(() => {
  // Note: Session management is now handled in individual commands using cy.session()
  // No need to preserve cookies manually in Cypress 12+

  // Set up common interceptors
  cy.intercept('GET', '/api/journals*').as('getJournals');
  cy.intercept('POST', '/api/journals').as('createJournal');
  cy.intercept('PUT', '/api/journals/*').as('updateJournal');
  cy.intercept('DELETE', '/api/journals/*').as('deleteJournal');

  // Set up test data interceptors
  cy.intercept('POST', '/api/test/**').as('testAPI');
  cy.intercept('DELETE', '/api/test/**').as('testCleanup');

  // Common viewport setup
  cy.viewport(1280, 720);
});

// Global afterEach hook
afterEach(() => {
  // Clean up test data if enabled
  if (Cypress.env('testDataCleanup')) {
    cy.log('Cleaning up test data...');
    // Implementation would clean up any test data created during the test
  }

  // Take screenshot on failure
  if (Cypress.currentTest.state === 'failed') {
    cy.screenshot(`failed-${Cypress.currentTest.title}`);
  }
});

// Global after hook
after(() => {
  cy.log('Test suite completed');
});

// Custom error handling
Cypress.on('fail', (error, runnable) => {
  // Log additional context on test failure
  console.log('Test failed:', runnable.title);
  console.log('Error:', error.message);

  // Add custom error information
  if (error.message.includes('Timed out')) {
    console.log('Timeout error - check if elements exist and are visible');
  }

  throw error;
});

// Performance monitoring
if (Cypress.env('enablePerformanceTests')) {
  beforeEach(() => {
    // Start performance monitoring
    cy.window().then((win) => {
      win.performance.mark('test-start');
    });
  });

  afterEach(() => {
    // End performance monitoring
    cy.window().then((win) => {
      win.performance.mark('test-end');
      win.performance.measure('test-duration', 'test-start', 'test-end');

      const measures = win.performance.getEntriesByType('measure');
      const testDuration = measures[measures.length - 1];

      if (testDuration.duration > 5000) {
        cy.log(`⚠️ Slow test detected: ${testDuration.duration}ms`);
      }
    });
  });
}

// Accessibility testing setup
if (Cypress.env('enableAccessibilityTests')) {
  beforeEach(() => {
    // Configure axe-core only if available
    cy.window().then((win) => {
      if (typeof win.axe !== 'undefined') {
        cy.configureAxe({
          rules: [
            {
              id: 'color-contrast',
              enabled: true
            },
            {
              id: 'keyboard-navigation',
              enabled: true
            }
          ]
        });
      }
    });
  });
}

// Network monitoring
beforeEach(() => {
  // Monitor failed network requests
  cy.intercept('**', (req) => {
    req.on('response', (res) => {
      if (res.statusCode >= 400) {
        cy.log(`❌ Failed request: ${req.method} ${req.url} - ${res.statusCode}`);
      }
    });
  });
});

// Custom Cypress configuration
Cypress.config('defaultCommandTimeout', 10000);
Cypress.config('requestTimeout', 10000);
Cypress.config('responseTimeout', 10000);

// Add custom CSS for better test visibility
Cypress.on('window:before:load', (win) => {
  // Add custom styles for test elements
  const style = win.document.createElement('style');
  style.innerHTML = `
    [data-testid] {
      outline: 1px dashed rgba(0, 255, 0, 0.3) !important;
    }

    [data-testid]:hover {
      outline: 2px solid rgba(0, 255, 0, 0.6) !important;
    }

    .cypress-highlight {
      background-color: rgba(255, 255, 0, 0.3) !important;
      border: 2px solid #ffff00 !important;
    }
  `;
  win.document.head.appendChild(style);
});

// Utility functions available globally
Cypress.Commands.add('waitForPageLoad', () => {
  cy.window().should('have.property', 'document');
  cy.document().should('have.property', 'readyState', 'complete');
});

Cypress.Commands.add('waitForFilament', () => {
  // Wait for Filament to be fully loaded
  cy.window().should('have.property', 'Livewire');
  cy.get('[wire\\:loading]').should('not.exist');
});

// Debug helpers
Cypress.Commands.add('debugTest', () => {
  cy.pause();
  cy.debug();
});

Cypress.Commands.add('logCurrentUrl', () => {
  cy.url().then((url) => {
    cy.log(`Current URL: ${url}`);
  });
});

// Test data helpers
Cypress.Commands.add('resetTestData', () => {
  cy.task('clearDatabase');
  cy.task('seedDatabase');
});

// Environment-specific configuration
if (Cypress.env('NODE_ENV') === 'development') {
  // Development-specific settings
  Cypress.config('video', false);
  Cypress.config('screenshotOnRunFailure', true);
} else if (Cypress.env('NODE_ENV') === 'production') {
  // Production/CI-specific settings
  Cypress.config('video', true);
  Cypress.config('screenshotOnRunFailure', true);
}

// Browser-specific configuration
Cypress.on('before:browser:launch', (browser, launchOptions) => {
  if (browser.name === 'chrome') {
    // Chrome-specific setup
    launchOptions.args.push('--disable-web-security');
    launchOptions.args.push('--allow-running-insecure-content');
  }

  return launchOptions;
});
