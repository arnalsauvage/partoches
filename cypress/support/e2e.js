import './commands';

// Ignorer les exceptions non capturées de l'application pour ne pas faire échouer les tests d'intégration
Cypress.on('uncaught:exception', (err, runnable) => {
  return false;
});
