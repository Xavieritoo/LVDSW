describe('Búsqueda de vuelos', () => {
    const appUrl = 'http://planitv0.test';

    const seleccionarCiudad = ({ boton, nombre }) => {
        const lista = boton === '#btn-origen' ? '#lista-origen' : '#lista-destino';

        cy.get(boton).should('be.visible').click();
        cy.get(lista)
            .contains('.ciudad-item', nombre, { timeout: 10000 })
            .scrollIntoView()
            .click({ force: true });
    };

    const formatearMes = (fecha) => {
        const ano = fecha.getFullYear();
        const mes = String(fecha.getMonth() + 1).padStart(2, '0');
        return `${ano}-${mes}`;
    };

    it('permite elegir origen, destino y lanzar la búsqueda', () => {
        cy.visit(`${appUrl}/destinos`);

        cy.contains('Planea tu vuelo').should('be.visible');

        seleccionarCiudad({
            boton: '#btn-origen',
            nombre: 'Madrid',
        });
        cy.get('#origen-texto').should('contain', 'Madrid');

        seleccionarCiudad({
            boton: '#btn-destino',
            nombre: 'Barcelona',
        });
        cy.get('#destino-texto').should('contain', 'Barcelona');

        cy.get('#input-mes-ida').invoke('val', '2027-01').trigger('input').trigger('change');
        cy.get('#input-mes-vuelta').invoke('val', '2027-01').trigger('input').trigger('change');

        cy.contains('button', 'Buscar').should('be.visible').click();

        cy.location('pathname').should('eq', '/destinos');
        cy.location('search').should('include', 'origen_id=');
        cy.location('search').should('include', 'destino_id=');
        cy.location('search').should('include', 'mes_ida=');

        cy.contains('Planea tu vuelo').should('be.visible');
        cy.get('#origen-texto').should('contain', 'Madrid');
        cy.get('#destino-texto').should('contain', 'Barcelona');
    });
});
