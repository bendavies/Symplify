<?php

require 'autoload.php';

require_once 'autoload.php';

include 'autoload.php';

include_once 'autoload.php';


require $variable;

require __DIR__ . $variable;

require __DIR__ . 'string';

?>
-----
<?php

require __DIR__.'/autoload.php';

require_once __DIR__.'/autoload.php';

include __DIR__.'/autoload.php';

include_once __DIR__.'/autoload.php';


require $variable;

require __DIR__ . $variable;

require __DIR__ . 'string';

?>
