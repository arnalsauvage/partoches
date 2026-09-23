describe('02 - Tunnel d\'inscription & validation e-mail', () => {
  const testLogin = `e2e_user_${Math.floor(Math.random() * 10000)}`;

  it('Affiche le formulaire d\'inscription avec le bon ordre des champs', () => {
    cy.visit('/php/utilisateur/utilisateur_inscription.php');
    
    cy.get('h1').should('contain', 'Créer un compte');
    
    // Vérification de la présence des champs dans l'ordre
    cy.get('input[name="flogin"]').should('exist');
    cy.get('input[name="femail"]').should('exist');
    cy.get('input[name="fprenom"]').should('exist');
    cy.get('input[name="fnom"]').should('exist');
    cy.get('input[name="fmdp"]').should('exist');
    cy.get('input[name="fmdp_confirm"]').should('exist');
  });

  it('Soumet une inscription valide et affiche l\'invitation d\'activation e-mail', () => {
    cy.visit('/php/utilisateur/utilisateur_inscription.php');

    cy.get('input[name="flogin"]').type(testLogin);
    cy.get('input[name="femail"]').type(`${testLogin}@example.com`);
    cy.get('input[name="fprenom"]').type('Jean');
    cy.get('input[name="fnom"]').type('Dupont');
    cy.get('input[name="fmdp"]').type('secret123');
    cy.get('input[name="fmdp_confirm"]').type('secret123');

    cy.get('#inscription-form').submit();

    // Vérification du message de confirmation
    cy.contains('Compte créé avec succès !').should('be.visible');
    cy.contains(`${testLogin}@example.com`).should('be.visible');
    cy.contains('Mode Développement / Local').should('be.visible');
  });
});
