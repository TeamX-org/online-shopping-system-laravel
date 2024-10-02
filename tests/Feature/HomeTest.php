<?php

it('returns a successful response from home page', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
