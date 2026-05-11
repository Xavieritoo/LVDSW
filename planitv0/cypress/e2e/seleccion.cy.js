describe('Selección de plan en la compra', () => {
  const appUrl = 'http://planitv0.test';
  const fechaIda = '2027-01-02';
  const fechaVuelta = '2027-01-09';

  it('elige vuelos y selecciona un plan', () => {
    // Paso 1: Búsqueda de vuelos
    cy.visit(`${appUrl}/destinos`);
    cy.contains('Planea tu vuelo').should('be.visible');

    // Seleccionar origen y destino
    cy.get('#btn-origen').click();
    cy.get('#lista-origen .ciudad-item').contains('Madrid').click({ force: true });
    cy.get('#btn-destino').click();
    cy.get('#lista-destino .ciudad-item').contains('Barcelona').click({ force: true });

    // Seleccionar meses
    cy.get('#input-mes-ida').invoke('val', '2027-01').trigger('input').trigger('change');
    cy.get('#input-mes-vuelta').invoke('val', '2027-01').trigger('input').trigger('change');

    // Buscar
    cy.contains('button', 'Buscar').click();
    cy.contains('Planea tu vuelo').should('be.visible');

    // Seleccionar fechas exactas
    cy.get('.cal-celda[data-seleccionable="1"][data-trayecto="ida"][data-fecha-iso="2027-01-02"]').first().click({ force: true });
    cy.get('.cal-celda[data-seleccionable="1"][data-trayecto="vuelta"][data-fecha-iso="2027-01-09"]').first().click({ force: true });

    // Continuar
    cy.contains('button', 'CONTINUAR').click();
    cy.location('pathname').should('eq', '/resultados');

    // Paso 2: Selección de vuelos y plan
    // Seleccionar primer vuelo de ida
    cy.get('#columnaResultadosIda .plan-action').first().click({ force: true });
    // Seleccionar primer vuelo de vuelta
    cy.get('#columnaResultadosVuelta .plan-action').first().click({ force: true });

    // El botón continuar compra debe estar habilitado
    cy.get('#btnContinuar').should('not.be.disabled').click();

    // Validar que llega a la página de pasajeros (o siguiente paso)
    cy.location('pathname').should('include', '/pasajeros');
  });
});
