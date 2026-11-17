<?php
function W($p,$c){$d=dirname($p);if(!is_dir($d))mkdir($d,0755,true);file_put_contents($p,$c);echo "Wrote: $p (".strlen($c)." bytes)".PHP_EOL;}
