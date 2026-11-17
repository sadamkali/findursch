<?php
function W($p, $b) { $d = dirname($p); if (!is_dir($d)) { mkdir($d, 0755, true); } $c = base64_decode($b); file_put_contents($p, $c); echo "Wrote: $p (" . strlen($c) . " bytes)" . PHP_EOL; }
W("d:\\\\FInd your school\\\\findursch\\\\_test_decoded.txt", "aGVsbG8gQGV4dGVuZHM=");
