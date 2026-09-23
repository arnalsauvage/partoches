module.exports = {
  e2e: {
    baseUrl: "http://127.0.0.1:8080",
    viewportWidth: 1280,
    viewportHeight: 720,
    video: false,
    screenshotOnRunFailure: true,
    specPattern: "cypress/e2e/**/*.cy.js",
    supportFile: "cypress/support/e2e.js",
    setupNodeEvents(on, config) {
      // Événements Node.js si besoin
    },
  },
};
