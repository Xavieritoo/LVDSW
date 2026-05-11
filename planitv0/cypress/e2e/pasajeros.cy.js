describe('Formulario de pasajeros', () => {
  const appUrl = 'http://planitv0.test';

  // Llega a /pasajeros pasando por el flujo completo
  const llegarAPasajeros = () => {
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
  };

  it('muestra el formulario de pasajeros', () => {
    llegarAPasajeros();

    cy.contains('Datos de pasajeros').should('be.visible');
    cy.get('#adultos_nombre_0').should('be.visible');
    cy.get('#adultos_apellidos_0').should('be.visible');
    cy.get('#adultos_fecha_0').should('be.visible');
    cy.get('#correo_contacto').should('be.visible');
  });

  it('rellena los datos del pasajero y el correo', () => {
    llegarAPasajeros();

    cy.get('#adultos_nombre_0').type('Ana');
    cy.get('#adultos_apellidos_0').type('García López');
    cy.get('#adultos_fecha_0').type('1990-05-15');
    cy.get('#correo_contacto').type('ana@ejemplo.com');

    // Los campos deben tener los valores introducidos
    cy.get('#adultos_nombre_0').should('have.value', 'Ana');
    cy.get('#adultos_apellidos_0').should('have.value', 'García López');
    cy.get('#correo_contacto').should('have.value', 'ana@ejemplo.com');
  });

  it('el correo es obligatorio', () => {
    llegarAPasajeros();

    // Rellenar nombre y apellidos pero dejar correo vacío
    cy.get('#adultos_nombre_0').type('Ana');
    cy.get('#adultos_apellidos_0').type('García López');
    cy.get('#adultos_fecha_0').type('1990-05-15');

    // Intentar enviar sin correo
    cy.contains('button', 'Continuar').click();

    // El navegador debe bloquear el envío por campo required
    // El campo correo debe tener foco o mostrar validación nativa
    cy.get('#correo_contacto').then(($el) => {
      expect($el[0].validity.valid).to.be.false;
    });

    // Seguimos en la misma página
    cy.location('pathname').should('include', '/pasajeros');
  });
});
