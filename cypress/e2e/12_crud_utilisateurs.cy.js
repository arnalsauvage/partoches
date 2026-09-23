describe('12 - E2E CRUD Utilisateurs', () => {
  beforeEach(() => {
    cy.login('admin', 'kazoo');
  });

  it('Affiche la liste des utilisateurs pour un administrateur', () => {
    cy.visit('/php/utilisateur/utilisateur_liste.php');
    cy.get('body').should('contain', 'Utilisateurs');
  });

  it('Affiche la fiche d\'édition d\'un utilisateur', () => {
    cy.visit('/php/utilisateur/utilisateur_form.php?id=1');
    cy.get('body').should('be.visible');
  });
});
