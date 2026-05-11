<?php

test('rutas publicas principales responden correctamente', function () {
    $this->get('/')->assertRedirect(route('destinos.index'));
    $this->get('/login')->assertOk();
    $this->get('/register')->assertOk();
    $this->get('/password/reset')->assertOk();
});

test('ruta area personal requiere autenticacion', function () {
    $this->get('/area-personal')
        ->assertRedirect(route('login'));
});

test('formulario nueva contrasena toma email de query string', function () {
    $this->get('/password/nueva?email=alumno@example.com')
        ->assertOk()
        ->assertViewIs('reset')
        ->assertViewHas('email', 'alumno@example.com');
});

test('formulario nueva contrasena toma email de sesion si no llega por query', function () {
    $this->withSession(['email_recuperacion' => 'sesion@example.com'])
        ->get('/password/nueva')
        ->assertOk()
        ->assertViewIs('reset')
        ->assertViewHas('email', 'sesion@example.com');
});

test('formulario nueva contrasena permite email nulo cuando no hay query ni sesion', function () {
    $this->get('/password/nueva')
        ->assertOk()
        ->assertViewIs('reset')
        ->assertViewHas('email', null);
});

test('ruta swagger responde correctamente', function () {
    $this->get('/swagger')->assertOk();
});
