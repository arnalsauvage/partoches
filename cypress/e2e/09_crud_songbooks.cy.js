describe('09 - E2E CRUD Songbooks & Portfolio', () => {
  beforeEach(() => {
    cy.clearCookies();
    cy.clearLocalStorage();
  });

  it('Affiche la vue Portfolio des recueils songbooks', () => {
    cy.visit('/php/songbook/songbook-portfolio.php');
    cy.get('body').should('contain', 'Songbook');
  });

  it('Affiche la liste complète des songbooks pour un utilisateur connecté', () => {
    cy.login('admin', 'kazoo');
    cy.visit('/php/songbook/songbook_liste.php');
    cy.get('body').should('contain', 'Recueils');
  });

  it('Consulte la fiche d\'un recueil songbook', () => {
    cy.visit('/php/songbook/songbook_voir.php?id=40');
    cy.get('body').should('be.visible');
  });
});
