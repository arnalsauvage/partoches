describe('US-003 — Module de Sauvegarde Globale et Export ZIP', () => {
    beforeEach(() => {
        cy.login('admin', 'kazoo');
    });

    it('Affiche le bouton de sauvegarde globale ZIP sur la page de paramétrage', () => {
        cy.visit('/php/admin/params.php');
        cy.contains('a', 'Sauvegarde Globale ZIP').should('be.visible')
            .should('have.attr', 'href', 'backup_export.php');
    });

    it('Déclenche la réponse HTTP 200 / octet stream lors du clic sur l\'export ZIP', () => {
        cy.request({
            method: 'HEAD',
            url: '/php/admin/backup_export.php',
            timeout: 120000
        }).then((response) => {
            expect(response.status).to.eq(200);
            expect(response.headers['content-type']).to.include('application/zip');
            expect(response.headers['content-disposition']).to.include('partoches-backup');
        });
    });
});
