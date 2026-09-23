describe('05 - Navigation & Pages Principales du Menu', () => {
  const pages = [
    { name: 'Médias', url: '/php/media/listeMedias.php' },
    { name: 'Chansons', url: '/php/chanson/chanson_liste.php?razFiltres=1' },
    { name: 'Strums', url: '/php/strum/strum_liste.php' },
    { name: 'Songbooks', url: '/php/songbook/songbook_liste.php' },
    { name: 'Liens', url: '/php/liens/lienurl_liste.php' },
    { name: 'Outils', url: '/html/diagrammes/' },
    { name: 'Playlists', url: '/php/playlist/playlist_liste.php' },
    { name: 'Utilisateurs', url: '/php/utilisateur/utilisateur_liste.php' }
  ];

  pages.forEach((page) => {
    it(`Charge la page ${page.name} (${page.url}) sans erreur`, () => {
      cy.visit(page.url);
      cy.url().should('include', page.url);
      cy.get('body').should('be.visible');
    });
  });
});
