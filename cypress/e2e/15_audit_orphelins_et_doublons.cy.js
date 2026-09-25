describe('US-002 — Audit et Détection des Fichiers Orphelins & Doublons (AJAX)', () => {
    beforeEach(() => {
        cy.login('admin', 'kazoo');
    });

    it('Affiche correctement le tableau de bord et charge les données en arrière-plan via AJAX', () => {
        cy.visit('/php/admin/audit_orphelins.php');

        // Titre et header Canopée
        cy.contains('h1', 'Audit Fichiers Orphelins').should('be.visible');
        cy.contains('a', 'Retour Paramétrage').should('be.visible');
        cy.contains('a', 'Sauvegarde Globale ZIP').should('be.visible');

        // Cartes de synthèse
        cy.contains('Fichiers Orphelins').should('be.visible');
        cy.contains('Doublons (MD5)').should('be.visible');

        // Onglets
        cy.contains('li.tab-dj', 'Fichiers Orphelins').should('be.visible');
        cy.contains('li.tab-dj', 'Fichiers Doublons').should('be.visible').click();

        // Bascule vers onglet Doublons (Lazy loading AJAX)
        cy.get('#dj-duplicates').should('have.class', 'active');
    });

    it('Répond correctement aux appels API JSON AJAX', () => {
        cy.request('/php/admin/audit_orphelins.php?action=scan_orphans').then((res) => {
            expect(res.status).to.eq(200);
            expect(res.body.success).to.be.true;
            expect(res.body).to.have.property('orphans');
            expect(res.body).to.have.property('totalSize');
        });

        cy.request('/php/admin/audit_orphelins.php?action=scan_duplicates').then((res) => {
            expect(res.status).to.eq(200);
            expect(res.body.success).to.be.true;
            expect(res.body).to.have.property('duplicates');
            expect(res.body).to.have.property('totalWastedSize');
        });
    });
});
