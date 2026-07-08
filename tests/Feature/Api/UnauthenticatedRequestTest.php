<?php

// Regression test: Laravel's default Authenticate middleware tries
// route('login') for unauthenticated requests that don't send
// "Accept: application/json" — this app is API-only with no such route, so
// that used to 500 (RouteNotFoundException) instead of returning a clean
// 401. Deliberately uses plain get(), not getJson(), since getJson() sends
// an Accept header that masked the bug.

it('returns 401, not a 500, for a plain unauthenticated request with no Accept header', function () {
    $this->get('/api/workers')->assertUnauthorized();
});
