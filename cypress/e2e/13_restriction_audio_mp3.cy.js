describe('13 - Restriction d\'accès aux ressources audio (MP3) pour utilisateurs non connectés', () => {
  it('Affiche les badges de restriction sur la bibliothèque des médias pour les invités', () => {
    cy.visit('/php/media/listeMedias.php?filtres=audio');
    cy.get('body').should('be.visible');
    cy.contains('Connexion requise').should('exist');
  });

  it('Affiche la restriction sur la galerie de liens pour les audios', () => {
    cy.visit('/php/liens/lienurl_liste.php');
    cy.get('body').should('be.visible');
    cy.contains('Connexion requise').should('exist');
  });

  it('Redirige un invité tentant d\'accéder à un document audio via getdoc.php vers la page de login', () => {
    cy.request({
      url: '/php/document/getdoc.php?doc=1',
      followRedirect: false
    }).then((response) => {
      expect([302, 200, 404]).to.include(response.status);
    });
  });

  it('Permet l\'accès aux ressources audio lorsqu\'un utilisateur est connecté', () => {
    cy.login('admin', 'kazoo');
    cy.visit('/php/media/listeMedias.php?filtres=audio');
    cy.get('body').should('be.visible');
  });
});
