<?php

namespace Illuminate\Support\Debug;

use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;

class Dumper
{
    /**
     * Dump a value with elegance.
     *
     * @param  mixed  $value
     * @return void
     */
    public function dump($value)
    {
        $dumper = 'cli' === PHP_SAPI ? new CliDumper : new HtmlDumper;

        $varCloner = new VarCloner;

        $dumper->dump($varCloner->cloneVar($value));
    }

}
