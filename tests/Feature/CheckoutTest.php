<?php

it('returns a response from checkout page', function () {
    $response = $this->get('/checkout');

    $response->assertStatus(200);
});
