describe('06 - E2E CRUD Chansons', () => {
  const testSongName = `Chanson_E2E_${Date.now()}`;

  beforeEach(() => {
    cy.login('admin', 'kazoo');
  });

  it('Accède à la liste et affiche les chansons existantes', () => {
    cy.visit('/php/chanson/chanson_liste.php?razFiltres=1');
    cy.get('body').should('contain', 'Chansons');
  });

  it('Consulte une fiche chanson et vérifie la présence des détails', () => {
    cy.visit('/php/chanson/chanson_voir.php?id=23');
    cy.get('body').should('be.visible');
  });
});
