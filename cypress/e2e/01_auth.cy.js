describe('01 - Authentification & Modal de connexion', () => {
  beforeEach(() => {
    cy.visit('/php/chanson/chanson_liste.php');
  });

  it('Affiche la popup de connexion au clic sur le bouton de header', () => {
    cy.get('#afficherPopup').should('be.visible').click();
    cy.get('#popup-login').should('be.visible');
    cy.get('#login').should('be.visible');
    cy.get('#pass').should('be.visible');
    cy.get('#btn-login-submit').should('be.visible');
  });

  it('Ferme la popup de connexion au clic sur le bouton fermer', () => {
    cy.get('#afficherPopup').click();
    cy.get('#popup-login').should('be.visible');
    cy.get('#btn-close-login').click();
    cy.get('#popup-login').should('not.be.visible');
  });

  it('Connecte un utilisateur valide (ex: membre)', () => {
    cy.login('membre', 'membre123');
    // Vérification que l'utilisateur est redirigé ou connecté
    cy.url().should('include', '/php/');
  });
});
