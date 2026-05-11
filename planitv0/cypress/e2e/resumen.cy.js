describe('Resumen final de la reserva', () => {
  const appUrl = 'http://planitv0.test';

  const toNumber = (text) => {
    const clean = text.replace(/\s|EUR/g, '').replace(/\./g, '').replace(',', '.');
    return Number.parseFloat(clean);
  };

  const llegarAResumen = () => {
    cy.visit(`${appUrl}/destinos`);
    cy.contains('Planea tu vuelo').should('be.visible');

    cy.get('#btn-origen').click();
    cy.get('#lista-origen .ciudad-item').contains('Madrid').click({ force: true });
    cy.get('#btn-destino').click();
    cy.get('#lista-destino .ciudad-item').contains('Barcelona').click({ force: true });

    cy.get('#input-mes-ida').invoke('val', '2027-01').trigger('input').trigger('change');
    cy.get('#input-mes-vuelta').invoke('val', '2027-01').trigger('input').trigger('change');

    cy.contains('button', 'Buscar').click();

    cy.get('.cal-celda[data-seleccionable="1"][data-trayecto="ida"][data-fecha-iso="2027-01-02"]').first().click({ force: true });
    cy.get('.cal-celda[data-seleccionable="1"][data-trayecto="vuelta"][data-fecha-iso="2027-01-09"]').first().click({ force: true });

    cy.contains('button', 'CONTINUAR').click();
    cy.location('pathname').should('eq', '/resultados');

    cy.get('#columnaResultadosIda .plan-action').first().click({ force: true });
    cy.get('#columnaResultadosVuelta .plan-action').first().click({ force: true });
    cy.get('#btnContinuar').should('not.be.disabled').click();

    cy.location('pathname').should('include', '/pasajeros');

    cy.get('#adultos_nombre_0').type('Ana');
    cy.get('#adultos_apellidos_0').type('Garcia Lopez');
    cy.get('#adultos_fecha_0').type('1990-05-15');
    cy.get('#correo_contacto').type('ana@ejemplo.com');
    cy.contains('button', 'Continuar').click();

    cy.location('pathname').should('include', '/equipajes');

    // Marcar al menos un equipaje para verlo reflejado en el resumen
    cy.get('.bag-check').first().check({ force: true });
    cy.contains('button', 'Continuar').click();

    cy.location('pathname').should('include', '/resumen');
  };

  it('muestra pasajeros, equipajes y el total correcto', () => {
    llegarAResumen();

    // Pasajeros
    cy.contains('h5', 'Pasajeros').should('be.visible');
    cy.contains('Adultos').should('be.visible');
    cy.contains('Contacto:').should('be.visible');

    // Equipajes
    cy.contains('h5', 'Equipaje extra facturado')
      .parent()
      .within(() => {
        cy.get('table tbody tr').its('length').should('be.greaterThan', 0);
      });

    // Total correcto: base + equipajes = total reserva
    cy.contains('h5', 'Total')
      .parent()
      .within(() => {
        cy.get('.summary-line strong').then(($values) => {
          const base = toNumber($values.eq(0).text());
          const equipajes = toNumber($values.eq(1).text());
          const total = toNumber($values.eq(2).text());

          expect(total).to.eq(base + equipajes);
        });
      });
  });
});
