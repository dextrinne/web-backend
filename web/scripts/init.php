<?php
function init($request = array(), $urlconf = array(), $db = null) {
    
    if ($db === null) {
        require_once(__DIR__ . '/db.php');
    }

    $response = array();

    $template = 'page';
    $c = array();

    require_once('functions.php'); 

    $q = isset($request['url']) ? $request['url'] : '';
    $method = isset($request['method']) ? $request['method'] : 'get';
    foreach ($urlconf as $url => $r) {
        $matches = array();
        if ($url == '' || $url[0] != '/') {
            if ($url != $q) {
                continue;
            }
        } else {
            if (!preg_match_all($url, $q, $matches)) {
                continue;
            }
        }

        if (isset($r['auth'])) {
            require_once($r['auth'] . '.php');
            $auth = auth($request, $r, $db); 
            if ($auth) {
                return $auth;
            }
        }

        if (isset($r['tpl'])) {
            $template = $r['tpl'];
        }

        if (!isset($r['module'])) {
            continue;
        }
        require_once($r['module'] . '.php');
        $func = sprintf('%s_%s', $r['module'], $method);
        if (!function_exists($func)) {
            continue;
        }

        $params = array('request' => $request);
        array_shift($matches);
        foreach ($matches as $key => $match) {
            $params[$key] = $match[0];
        }

        /*if ($result = call_user_func_array($func, $params)) {*/
        $ref = new ReflectionFunction($func);
        $orderedParams = [];

        foreach ($ref->getParameters() as $param) {
            $name = $param->getName();
            if (isset($params[$name])) {
                $orderedParams[] = $params[$name];
            } elseif ($param->isDefaultValueAvailable()) {
                $orderedParams[] = $param->getDefaultValue();
            } else {
                // Можно выбросить исключение или поставить null
                $orderedParams[] = null;
            }
        }

        if ($result = call_user_func_array($func, $orderedParams)) {
            if (is_array($result)) {
                $response = array_merge($response, $result);
                if (!empty($response['headers'])) {
                    return $response;
                }
            } else {
                $c['#content'][$r['module']] = $result;
            }
        }
    }
    if (!empty($c)) {
        $c['#request'] = $request;
        $response['entity'] = theme($template, $c);
    } else {
        $response = not_found();
    }
    $response['headers']['Content-Type'] = 'text/html; charset=' . conf('charset');

    return $response;
}

function conf($key)
{
    global $conf;
    return isset($conf[$key]) ? $conf[$key] : FALSE;
}

function url($addr = '', $params = array())
{
    global $conf;
    if ($addr == '' && isset($_GET['q'])) {
        $addr = strip_tags($_GET['q']);
    }
    $clean = conf('clean_urls');
    $r = $clean ? '/' : '?q=';
    $r .= strip_tags($addr);
    if (count($params) > 0) {
        $r .= $clean ? '?' : '&';
        $r .= implode('&', $params);
    }
    return $r;
}

function redirect($l = NULL)
{
    if (is_null($l)) {
        $location = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    } else {
        $location = 'http://' . $_SERVER['HTTP_HOST'] . conf('basedir') . url($l);
    }
    return array('headers' => array('Location' => $location));
}

function access_denied()
{
    return array(
        'headers' => array('HTTP/1.1 403 Forbidden'),
        'entity' => theme('403'),
    );
}

function not_found()
{
    return array(
        'headers' => array('HTTP/1.1 404 Not Found'),
        'entity' => theme('404'),
    );
}

function theme($t, $c = array())
{
    $template = conf('theme') . '/' . str_replace('/', '_', $t) . '.tpl.php';
    if (!file_exists($template)) {
        return implode('', $c);
    }
    ob_start();
    include $template;
    $contents = ob_get_contents();
    ob_end_clean();
    return $contents;
}
