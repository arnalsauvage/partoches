describe('08 - E2E CRUD Strums (Rythmes)', () => {
  beforeEach(() => {
    cy.clearCookies();
    cy.clearLocalStorage();
  });

  it('Affiche la liste des strums et rythmiques', () => {
    cy.visit('/php/strum/strum_liste.php');
    cy.get('body').should('contain', 'STRUMS');
  });

  it('Accède au formulaire de création de strum pour les utilisateurs habilités', () => {
    cy.login('admin', 'kazoo');
    cy.visit('/php/strum/strum_form.php?id=1');
    cy.get('body').should('be.visible');
  });
});
