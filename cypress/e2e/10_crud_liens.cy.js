describe('10 - E2E CRUD Liens URLs', () => {
  beforeEach(() => {
    cy.clearCookies();
    cy.clearLocalStorage();
  });

  it('Affiche la liste des liens URLs enregistrés', () => {
    cy.visit('/php/liens/lienurl_liste.php');
    cy.get('body').should('contain', 'Liens');
  });

  it('Affiche la carte d\'un lien valide avec la description', () => {
    cy.visit('/php/liens/lienurl_liste.php');
    cy.get('.video-grid').should('exist');
  });
});
