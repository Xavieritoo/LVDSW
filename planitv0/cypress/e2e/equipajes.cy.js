describe('Selección de equipajes', () => {
  const appUrl = 'http://planitv0.test';

  const llegarAEquipajes = () => {
    cy.visit(`${appUrl}/destinos`);
    cy.contains('Planea tu vuelo').should('be.visible');

    cy.get('#btn-origen').click();
    cy.get('#lista-origen .ciudad-item').contains('Madrid').click({ force: true });
    cy.get('#btn-destino').click();
    cy.get('#lista-destino .ciudad-item').contains('Barcelona').click({ force: true });

    cy.get('#input-mes-ida').invoke('val', '2027-01').trigger('input').trigger('change');
    cy.get('#input-mes-vuelta').invoke('val', '2027-01').trigger('input').trigger('change');

    cy.contains('button', 'Buscar').click();
    cy.contains('Planea tu vuelo').should('be.visible');

    cy.get('.cal-celda[data-seleccionable="1"][data-trayecto="ida"][data-fecha-iso="2027-01-02"]').first().click({ force: true });
    cy.get('.cal-celda[data-seleccionable="1"][data-trayecto="vuelta"][data-fecha-iso="2027-01-09"]').first().click({ force: true });

    cy.contains('button', 'CONTINUAR').click();
    cy.location('pathname').should('eq', '/resultados');

    cy.get('#columnaResultadosIda .plan-action').first().click({ force: true });
    cy.get('#columnaResultadosVuelta .plan-action').first().click({ force: true });

    cy.get('#btnContinuar').should('not.be.disabled').click();
    cy.location('pathname').should('include', '/pasajeros');

    cy.get('#adultos_nombre_0').type('Ana');
    cy.get('#adultos_apellidos_0').type('García López');
    cy.get('#adultos_fecha_0').type('1990-05-15');
    cy.get('#correo_contacto').type('ana@ejemplo.com');

    cy.contains('button', 'Continuar').click();
    cy.location('pathname').should('include', '/equipajes');
  };

  it('muestra la página de equipajes con opciones', () => {
    llegarAEquipajes();

    cy.contains('Equipajes facturados').should('be.visible');
    cy.get('.bag-check').should('have.length.greaterThan', 0);
    cy.get('#totalEquipajes').should('be.visible');
    cy.get('#totalSeccion').should('be.visible');
  });

  it('al marcar un equipaje el total cambia', () => {
    llegarAEquipajes();

    // Leer el total inicial
    cy.get('#totalSeccion').invoke('text').then((textoInicial) => {
      // Marcar el primer checkbox de equipaje
      cy.get('.bag-check').first().check({ force: true });

      // El total debe haber cambiado
      cy.get('#totalSeccion').invoke('text').should('not.eq', textoInicial);
    });
  });

  it('al desmarcar el equipaje el total vuelve al valor original', () => {
    llegarAEquipajes();

    cy.get('#totalSeccion').invoke('text').then((textoInicial) => {
      cy.get('.bag-check').first().check({ force: true });
      cy.get('#totalSeccion').invoke('text').should('not.eq', textoInicial);

      cy.get('.bag-check').first().uncheck({ force: true });
      cy.get('#totalSeccion').invoke('text').should('eq', textoInicial);
    });
  });
});
