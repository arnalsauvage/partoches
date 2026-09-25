describe('14 - Affichage des images Médias/Chansons & Formulaire d\'édition Admin', () => {
  it('Visiteur - Affiche la page médias et vérifie la présence des images de cartes', () => {
    cy.visit('/php/media/listeMedias.php');
    cy.get('body').should('be.visible');
    cy.get('.media-card img').should('have.length.at.least', 1);
    cy.get('.media-card img').first().should('be.visible').and(($img) => {
      expect($img[0].naturalWidth).to.be.greaterThan(0);
    });
  });

  it('Visiteur - Affiche la page chansons et vérifie la présence des images de pochettes', () => {
    cy.visit('/php/chanson/chanson_liste.php?razFiltres=1');
    cy.get('body').should('be.visible');
    cy.get('.canopee-card-img img, .img-thumbnail').should('have.length.at.least', 1);
    cy.get('.canopee-card-img img, .img-thumbnail').first().should('be.visible').and(($img) => {
      expect($img[0].naturalWidth).to.be.greaterThan(0);
    });
  });

  it('Admin - Connecté, accède au formulaire d\'édition de chanson avec champs, image, médias et strums', () => {
    cy.login('admin', 'kazoo');
    cy.visit('/php/chanson/chanson_form.php?id=23');
    cy.get('body').should('be.visible');

    // 1. Vérification des champs principaux du formulaire
    cy.get('input[name="fnom"]').should('be.visible');
    cy.get('input[name="finterprete"]').should('be.visible');

    // 2. Vérification de la présence de la section Pochette / Image
    cy.get('img').should('exist');

    // 3. Vérification des blocs Médias et Strums
    cy.contains(/médias|documents|fichiers/i).should('exist');
    cy.contains(/rythmique|strum/i).should('exist');
  });
});
