describe('04 - Songbooks & Portfolio', () => {
  beforeEach(() => {
    cy.clearCookies();
    cy.clearLocalStorage();
  });

  it('Affiche la liste des recueils dans le portfolio Songbook', () => {
    cy.visit('/php/songbook/songbook-portfolio.php');

    // Vérification de la présence des titres / cartes songbook
    cy.get('body').invoke('text').should('match', /Songbook|Recueil|Morceaux/i);
  });
});
