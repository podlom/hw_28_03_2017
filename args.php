<?php
/**
 * Created by PhpStorm.
 * User: Shkodenko
 * Date: 23.03.2017
 * Time: 20:39
 * PHP v.7+
 */

function funArgs(...$args) {
    echo 'Arguments: <pre>' . var_export($args, 1) . '</pre>';
}

funArgs('Test argument 1', false, 32, 3.14159, ['a', 'b', 'c', 'd']);
