describe('Página principal', () => {

    it('Carga correctamente', () => {

        cy.visit('http://planitv0.test/destinos')

        cy.contains('Planea tu vuelo')

    })

})