<?php

it('returns a successful response from cart page', function () {
    $response = $this->get('/cart');

    $response->assertStatus(200);
});
