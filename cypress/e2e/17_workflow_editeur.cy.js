describe('17 - Tests E2E Rôle Éditeur (Cycle complet Chanson & Songbook)', () => {
  const timestamp = Date.now();
  const testSongName = `Chanson_E2E_${timestamp}`;
  const testSongbookName = `Songbook_E2E_${timestamp}`;
  let chansonId = null;
  let songbookId = null;

  beforeEach(() => {
    // Connexion au site avec privilèges éditeur / admin
    cy.login('admin', 'kazoo');
  });

  after(() => {
    // Nettoyage de sécurité si jamais un test intermédiaire a échoué
    if (songbookId) {
      cy.visit(`/php/songbook/songbook_get.php?id=${songbookId}&mode=SUPPR`);
    }
    if (chansonId) {
      cy.visit(`/php/chanson/chanson_post.php?id=${chansonId}&mode=SUPPR`);
    }
  });

  it('1. Créer une nouvelle chanson', () => {
    cy.visit('/php/chanson/chanson_form.php');
    cy.get('#fnom').should('be.visible').clear().type(testSongName);
    cy.get('#finterprete').clear().type('Artiste E2E Initial');
    cy.get('#fannee').clear().type('2026');
    cy.get('#fpublication').check({ force: true });
    cy.get('#btn-valider-chanson').click();

    // Vérifie la redirection et capture l'ID généré
    cy.url().should('include', 'chanson_form.php?id=');
    cy.location('search').then((search) => {
      const params = new URLSearchParams(search);
      chansonId = params.get('id');
      expect(chansonId).to.not.be.null;
      expect(Number(chansonId)).to.be.greaterThan(0);
    });
    cy.get('#fnom').should('have.value', testSongName);
  });

  it('2. Modifier la chanson existante', () => {
    expect(chansonId, 'ID de la chanson doit être défini').to.not.be.null;
    cy.visit(`/php/chanson/chanson_form.php?id=${chansonId}`);
    cy.get('#finterprete').clear().type('Artiste E2E Modifié');
    cy.get('#btn-valider-chanson').click();

    cy.visit(`/php/chanson/chanson_form.php?id=${chansonId}`);
    cy.get('#finterprete').should('have.value', 'Artiste E2E Modifié');
  });

  it('3. Ajouter un fichier à la chanson', () => {
    expect(chansonId).to.not.be.null;
    cy.visit(`/php/chanson/chanson_form.php?id=${chansonId}`);
    cy.get('a[href="#tabs-2"]').click();

    // Upload d'un fichier de test
    const dummyFileContent = 'Titre: Test Chanson E2E\nAccords: C G Am F';
    cy.get('#tabs-2 form[action="chanson_upload.php"] input[name="fichierUploade"]').first().selectFile({
      contents: Cypress.Buffer.from(dummyFileContent),
      fileName: 'e2e-partition.crd',
      mimeType: 'text/plain',
    });
    cy.get('#tabs-2 form[action="chanson_upload.php"] button[type="submit"]').first().click();

    // Retour sur l'onglet documents pour vérifier la présence du fichier
    cy.visit(`/php/chanson/chanson_form.php?id=${chansonId}`);
    cy.get('a[href="#tabs-2"]').click();
    cy.get('#tabs-2').should('contain', 'e2e-partition');
  });

  it('4. Supprimer un fichier de la chanson (déplacement vers la corbeille)', () => {
    expect(chansonId).to.not.be.null;
    cy.visit(`/php/chanson/chanson_form.php?id=${chansonId}`);
    cy.get('a[href="#tabs-2"]').click();

    // Clic sur l'icône poubelle du document attaché
    cy.get('#tabs-2 .doc-actions button.btn-danger').first().click();
    cy.get('#btnConfirmAction').should('be.visible').click();

    // Attendre la redirection de chanson_post.php vers chanson_form.php
    cy.url().should('include', `chanson_form.php?id=${chansonId}`);
    cy.get('a[href="#tabs-2"]').click();
    cy.get('#tabs-2').should('contain', 'Corbeille');
    cy.get('#tabs-2').should('contain', '(Orphelin)');
  });

  it('5. Supprimer définitivement le fichier de la corbeille', () => {
    expect(chansonId).to.not.be.null;
    cy.visit(`/php/chanson/chanson_form.php?id=${chansonId}`);
    cy.get('a[href="#tabs-2"]').click();

    // Dans la corbeille, clic sur supprimer définitivement
    cy.get('#tabs-2 .list-group-item:contains("(Orphelin)") button.btn-danger').first().click();
    cy.get('#btnConfirmAction').should('be.visible').click();

    // Attendre la redirection puis vérifier que la corbeille est vide
    cy.url().should('include', `chanson_form.php?id=${chansonId}`);
    cy.get('a[href="#tabs-2"]').click();
    cy.get('#tabs-2').should('contain', 'La corbeille est vide.');
  });

  it('6. Créer un nouveau songbook', () => {
    cy.visit('/php/songbook/songbook_form.php');
    cy.get('#fnom').should('be.visible').clear().type(testSongbookName);
    cy.get('#fdescription').clear().type('Description Songbook E2E');
    cy.get('form.form-dj-reset button[type="submit"]').click();

    // Vérifie la redirection et capture l'ID du songbook
    cy.url().should('include', 'songbook_form.php?id=');
    cy.location('search').then((search) => {
      const params = new URLSearchParams(search);
      songbookId = params.get('id');
      expect(songbookId).to.not.be.null;
      expect(Number(songbookId)).to.be.greaterThan(0);
    });
    cy.get('#fnom').should('have.value', testSongbookName);
  });

  it('7. Modifier le songbook', () => {
    expect(songbookId, 'ID du songbook doit être défini').to.not.be.null;
    cy.visit(`/php/songbook/songbook_form.php?id=${songbookId}`);
    cy.get('#fdescription').clear().type('Description E2E Mise à jour');
    cy.get('form.form-dj-reset button[type="submit"]').click();

    cy.visit(`/php/songbook/songbook_form.php?id=${songbookId}`);
    cy.get('#fdescription').should('have.value', 'Description E2E Mise à jour');
  });

  it('8. Ajouter un fichier propre au songbook', () => {
    expect(songbookId).to.not.be.null;
    cy.visit(`/php/songbook/songbook_form.php?id=${songbookId}`);

    // Upload d'un fichier PDF propre au recueil
    const dummyPdfContent = '%PDF-1.4 Test PDF Recueil E2E %%EOF';
    cy.get('form[action="songbook_upload.php"] input[name="fichierUploade"]').selectFile({
      contents: Cypress.Buffer.from(dummyPdfContent),
      fileName: 'e2e-recueil.pdf',
      mimeType: 'application/pdf',
    });
    cy.get('form[action="songbook_upload.php"] button[type="submit"]').click();

    // Vérifie la présence du fichier dans les documents du recueil
    cy.visit(`/php/songbook/songbook_form.php?id=${songbookId}`);
    cy.get('.sb-list-item').should('contain', 'e2e-recueil');
  });

  it('9. Ajouter un document (partition) au sommaire du songbook', () => {
    expect(songbookId).to.not.be.null;
    cy.visit(`/php/songbook/songbook_form.php?id=${songbookId}`);

    // Utilisation du formulaire de liaison de document (#formFinalAjout)
    // On associe un document existant (ex: id=1 ou recherche)
    cy.get('#inputDocFinal').invoke('val', '1');
    cy.get('#formFinalAjout').submit();

    // Vérifie l'apparition dans le sommaire ordonnable
    cy.visit(`/php/songbook/songbook_form.php?id=${songbookId}`);
    cy.get('#sortable .sb-sortable-item').should('have.length.at.least', 1);
  });

  it('10. Supprimer la chanson et le songbook créés (Nettoyage)', () => {
    // Suppression du songbook
    if (songbookId) {
      cy.visit(`/php/songbook/songbook_get.php?id=${songbookId}&mode=SUPPR`);
      cy.visit('/php/songbook/songbook_liste.php');
      cy.get('body').should('not.contain', testSongbookName);
      songbookId = null;
    }

    // Suppression de la chanson
    if (chansonId) {
      cy.visit(`/php/chanson/chanson_post.php?id=${chansonId}&mode=SUPPR`);
      cy.visit('/php/chanson/chanson_liste.php?razFiltres=1');
      cy.get('body').should('not.contain', testSongName);
      chansonId = null;
    }
  });
});
