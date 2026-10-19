<?php

test('self registration is closed', function () {
    $this->get('/register')->assertNotFound();
    $this->post('/register', ['name' => 'Penyusup', 'email' => 'x@example.test', 'password' => 'password-123'])->assertStatus(404);
});
