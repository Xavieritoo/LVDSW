describe('Smoke test', () => {
  it('loads example.com', () => {
    cy.visit('https://example.com');
    cy.contains('Example Domain').should('be.visible');
  });
});
