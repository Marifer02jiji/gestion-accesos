<?php

/*
|--------------------------------------------------------------------------
| Matriz autorizador → solicitantes (usuario SAM, sin dominio)
|--------------------------------------------------------------------------
| Solo los usuarios listados bajo cada autorizador pueden ser aprobados
| o rechazados por él. Nunca las propias solicitudes.
|
| Nota: las solicitudes de "v" las autoriza hugo.rm, no mauro.
*/

return [
    'matriz' => [
        'mauro' => [
            'hugo.rm',
            'imillanf',
        ],
        'hugo.rm' => [
            'rcanor',
            'mauro',
            'v',
        ],
        'jmillanf' => [
            'accesosittol',
            'mauro',
        ],
    ],
];
