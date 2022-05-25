<?php

function dd($V, $E = 0) {
    echo!is_array($V) ? '' : '<pre>';
    echo ('</br>');
    if (!is_array($V))
        echo $V ?: 'empty';
    else
        var_dump($V ?: 'empty');
    echo!is_array($V) ? '' : '</pre>';
    echo ('</br>');
    if ($E == 1)
        exit();
}

function FuncCallFrom() {
    $dbt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
    return $caller = isset($dbt[2]['function']) ? (isset($dbt[2]['class']) ? trim(str_replace('\\','/',($dbt[2]['file'])),Root) . '@' . ($dbt[2]['class'] . $dbt[2]['type']) : '') . $dbt[2]['function'] : null;
}

function Response($S, $M, $D, Exc $E = null) {
    /*     */
    View('Response', [
        'Status' => $S,
        'Msg' => $M,
        'Data' => $D,
        'SubView' => ( View('About', ['Status' => $S], true)),
        'Exc' => $E,
            ]
    );
    /* */
}

/**
 * <p>Render Template e.g. : <code>View('Auth/Login', ['Email'=>'mail@DhruvJoshi.Dev','Mobile'=>9999999999])</code>.</p>
 * @param string $FileName <p> View Path</p>
 * @param array $Data <p>any data you can view</p>
 * @param bool $ReturnContent <p>default is false</p>
 * @return HTML string|View
 * @Link https://DhruvJoshi.Dev/Functions/View()
 * @Since 27-10-2021
 * @DevelopBy Dev.Dhruv Joshi
 */
//function View(string $string, string $token): string|false {}
function View(string $File = '', array $D = [], bool $ReturnContent = false): string|false|null  {
    if ($ReturnContent == true)
        return (new View($File, ($D ?: []), $ReturnContent))->Content();
    else
        echo (new View($File, ($D ?: []), $ReturnContent))->Render();
    return '';
}
