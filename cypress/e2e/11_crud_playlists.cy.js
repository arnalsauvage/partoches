describe('11 - E2E CRUD Playlists', () => {
  beforeEach(() => {
    cy.login('admin', 'kazoo');
  });

  it('Affiche la liste des playlists', () => {
    cy.visit('/php/playlist/playlist_liste.php');
    cy.get('body').should('contain', 'Playlist');
  });

  it('Affiche le formulaire de création/édition de playlist', () => {
    cy.visit('/php/playlist/playlist_form.php');
    cy.get('body').should('be.visible');
  });
});
