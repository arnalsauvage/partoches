describe('07 - E2E CRUD Médias', () => {
  beforeEach(() => {
    cy.clearCookies();
    cy.clearLocalStorage();
  });

  it('Affiche la liste des médias avec les cartes et le bouton de filtres', () => {
    cy.visit('/php/media/listeMedias.php');
    cy.get('body').should('contain', 'Nos dernières publications');
    cy.get('.media-card').should('have.length.greaterThan', 0);
  });

  it('Permet de déplier la console de filtres', () => {
    cy.visit('/php/media/listeMedias.php');
    cy.get('.toggle-filters-btn').click();
    cy.get('#filterConsole').should('be.visible');
  });
});
