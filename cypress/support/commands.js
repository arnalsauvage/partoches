// Commande personnalisée pour la connexion utilisateur
Cypress.Commands.add('login', (username, password) => {
  cy.visit('/php/chanson/chanson_liste.php');
  cy.get('#afficherPopup').click();
  cy.get('#popup-login').should('be.visible');
  cy.get('#login').clear().type(username);
  cy.get('#pass').clear().type(password);
  cy.get('#btn-login-submit').click();
  cy.get('.glyphicon-off').should('be.visible');
});
