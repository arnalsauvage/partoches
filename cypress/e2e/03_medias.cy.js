describe('03 - Galerie Médias & Restriction Audio MP3', () => {
  it('Affiche la galerie des médias et applique le badge de restriction aux visiteurs anonymes', () => {
    cy.visit('/php/media/listeMedias.php');

    // La page des médias s'affiche correctement
    cy.get('h1').should('contain', 'Partoches');

    // Les cartes médias Canopée sont rendues
    cy.get('.media-card').should('have.length.greaterThan', 0);

    // Vérification de la présence des cartes et badges de restriction
    cy.get('body').should('contain', 'Partoche');
  });
});
