<?php

test('guest is redirected to login from dashboard', function () {
    $this->get('/')->assertRedirect('/login');
});
