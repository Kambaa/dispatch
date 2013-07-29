<?php
include '../src/dispatch.php';
include './helpers.php';

define('URL', 'http://localhost:1234');

start_http('0.0.0.0', '1234', 'app.php');

/*-------------
 * local tests
 */

test('config setting and getting', function () {
  config('one', 1);
  config('false', false);
  assert(config('one') === 1);
  assert(config('false') === false);
});

test('site path setting and getting', function () {
  config('dispatch.url', 'http://localhost:8888/mysite/');
  assert(site() === 'http://localhost:8888/mysite/');
  assert(site(true) === '/mysite');
});

test('url encoding', function () {
  $s = 'name=jaydee&age=34';
  assert(u($s) === urlencode($s));
});

test('html escaping', function () {
  assert(h('&') === '&amp;');
});

test('cross-scope values', function () {
  scope('one', 1);
  call_user_func(function () {
    assert(scope('one') === 1);
  });
});

/*--------------
 * remote tests
 */

test('error triggers', function () {
  $res = curly('GET', URL.'/error');
  assert(preg_match('/500 page error/i', $res));
});

test('custom error handler', function () {
  $res = curly('GET', URL.'/not-found');
  assert(preg_match('/file not found/', $res));
});

test('GET handler', function () {
  $res = curly('GET', URL.'/index');
  assert(preg_match('/GET route test/', $res));
});

test('POST handler', function () {
  $res = curly('POST', URL.'/index');
  assert(preg_match('/POST route test/i', $res));
});

test('PUT handler', function () {
  $res = curly('PUT', URL.'/index');
  assert(preg_match('/PUT route test/i', $res));
});

test('DELETE handler', function () {
  $res = curly('DELETE', URL.'/index/1');
  assert(preg_match('/DELETE route test/i', $res));
});

test('302 redirect (default)', function () {
  $res = curly('GET', URL.'/redirect/302');
  assert(preg_match('/302 found/i', $res));
  assert(preg_match('/Location: \/index/i', $res));
});

test('301 redirect', function () {
  $res = curly('GET', URL.'/redirect/301');
  assert(preg_match('/301 moved permanently/i', $res));
  assert(preg_match('/Location: \/index/i', $res));
});

test('route filter', function () {
  $res = curly('GET', URL.'/index/123');
  assert(preg_match('/id found/i', $res));
  assert(preg_match('/id = 123/i', $res));
});

test('cookie setting', function () {
  $res = curly('GET', URL.'/cookie-set');
  assert(preg_match('/set-cookie: cookie=/i', $res));
  $res = curly('GET', URL.'/cookie-get');
  assert(preg_match('/cookie=123/i', $res));
});

test('params fetching', function () {
  $res = curly('GET', URL.'/params?one=1&two=2');
  assert(preg_match('/one=1/', $res));
  assert(preg_match('/two=2/', $res));
});

test('flash messages', function () {
  curly('GET', URL.'/flash-set');
  $res = curly('GET', URL.'/flash-get');
  assert(preg_match('/message=success/i', $res));
});
?>
