<?php

it('returns a successful response from products page', function () {
    $response = $this->get('/products');

    $response->assertStatus(200);
});
