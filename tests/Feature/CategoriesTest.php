<?php

it('returns a successful response from categories', function () {
    $response = $this->get('/categories');

    $response->assertStatus(200);
});
