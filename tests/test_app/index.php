<?php
declare(strict_types=1);

$test = $_GET['test'] ?? 'default';
header('X-Test: ' . $test);
switch ($test) {
    case 'die':
        die('I am dead');

    case 'fail':
        throw new \RuntimeException();

    case 'redirect':
        header('Location: /?test=text');
        break;

    case 'post':
        $data = $_POST;
        echo json_encode($data);
        break;

    case 'html_error404':
        header('HTTP/1.0 404 Not Found');
        echo '<html><head><title>Error 404</title></head><body><h1>Not Found</h1></body></html>';
        break;

    case 'html_error500':
        header('HTTP/1.0 500 Server Error');
        echo '<html><head><title>Error 500</title></head><body><h1>Server Error</h1></body></html>';
        break;

    case 'html':
        header('Content-Type: text/html');
        echo '<html><head><title>Test Page</title></head><body><h1>Foo</h1></body></html>';
        break;

    case 'json':
        header('Content-Type: application/json');
        echo json_encode(['foo' => 'bar']);
        break;

    case 'json_error404':
        header('HTTP/1.0 404 Not Found');
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Test error message']);
        break;

    case 'json_error500':
        header('HTTP/1.0 500 Server Error');
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Test error message']);
        break;

    case 'json_malformed':
        header('Content-Type: application/json');
        $json = json_encode(['error' => 'Test error message']);
        echo str_shuffle($json);
        break;

    case 'xml':
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<note>
  <to>Tove</to>
  <from>Jani</from>
  <heading>Reminder</heading>
  <body>Don't forget me this weekend!</body>
</note>
XML;
        header('Content-Type: application/xml');
        echo $xml;
        break;

    case 'custom_headers':
        header('X-Custom-Header: bar');
        header('X-Foo: bar1');
        header('X-Foo: bar2');
        break;

    case 'text':
        header('Content-Type: text/plain');
        echo 'This is a test';
        break;

    case 'empty':
        break;

    default:
        $html = '';
        $tests = [
            'html', 'html_error404', 'html_error500', 'json', 'json_malformed', 'json_error404', 'json_error500',
            'xml', 'text', 'empty', 'fail', 'die', 'custom_headers',
        ];
        foreach ($tests as $test) {
            $html .= '<li><a href="/?test=' . $test . '">' . $test . '</a></li>';
        }
        header('Content-Type: text/html');
        echo '<html><head><title>All Tests</title></head><body><ul>' . $html . '</ul></body></html>';
        break;
}

exit(0);
