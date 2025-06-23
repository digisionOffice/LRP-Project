const { defineConfig } = require('cypress')

module.exports = defineConfig({
    e2e: {
        // Base URL for the application
        baseUrl: 'https://lrp.test/',

        // Viewport settings
        viewportWidth: 1280,
        viewportHeight: 720,

        // Test files pattern
        specPattern: 'cypress/e2e/**/*.cy.{js,jsx,ts,tsx}',

        // Support file
        supportFile: 'cypress/support/e2e.js',

        // Screenshots and videos
        screenshotsFolder: 'cypress/screenshots',
        videosFolder: 'cypress/videos',

        // Test settings
        defaultCommandTimeout: 10000,
        requestTimeout: 10000,
        responseTimeout: 10000,
        pageLoadTimeout: 30000,

        // Retry settings
        retries: {
            runMode: 2,
            openMode: 0
        },

        // Environment variables
        env: {
            // API endpoints
            apiUrl: 'http://localhost:8000/api',

            // Test user credentials
            adminEmail: 'admin@example.com',
            adminPassword: 'password',
            userEmail: 'user@example.com',
            userPassword: 'password',

            // Feature flags
            enableAccessibilityTests: false,
            enablePerformanceTests: false,

            // Test data settings
            testDataCleanup: true,
            createTestData: true
        },

        setupNodeEvents(on, config) {
            // Task definitions for custom operations
            on('task', {
                // Database operations
                clearDatabase() {
                    // Implementation would connect to test database and clear test data
                    console.log('Clearing test database...');
                    return null;
                },

                seedDatabase() {
                    // Implementation would seed test database with required data
                    console.log('Seeding test database...');
                    return null;
                },

                // File operations
                readFile(filename) {
                    return require('fs').readFileSync(filename, 'utf8');
                },

                // Log operations
                log(message) {
                    console.log(message);
                    return null;
                }
            });

            // Browser launch options
            on('before:browser:launch', (browser = {}, launchOptions) => {
                if (browser.name === 'chrome') {
                    // Chrome-specific options
                    launchOptions.args.push('--disable-dev-shm-usage');
                    launchOptions.args.push('--no-sandbox');
                    launchOptions.args.push('--disable-gpu');

                    // For headless mode
                    if (browser.isHeadless) {
                        launchOptions.args.push('--window-size=1280,720');
                    }
                }

                return launchOptions;
            });

            // Test result processing
            on('after:spec', (spec, results) => {
                if (results && results.video) {
                    // Only keep videos for failed tests
                    if (results.stats.failures === 0) {
                        require('fs').unlinkSync(results.video);
                    }
                }
            });

            return config;
        },

        // Experimental features
        experimentalStudio: true,

        // Test isolation
        testIsolation: true,

        // Browser settings
        chromeWebSecurity: false,

        // Network settings
        blockHosts: [
            '*.google-analytics.com',
            '*.googletagmanager.com',
            '*.facebook.com',
            '*.twitter.com'
        ],

        // Exclude patterns
        excludeSpecPattern: [
            '**/*.hot-update.js',
            '**/node_modules/**',
            '**/vendor/**'
        ]
    },

    component: {
        devServer: {
            framework: 'vue',
            bundler: 'vite',
        },
        specPattern: 'resources/js/**/*.cy.{js,ts,jsx,tsx}',
        supportFile: 'cypress/support/component.js'
    }
});
