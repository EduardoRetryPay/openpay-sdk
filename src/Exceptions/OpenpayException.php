<?php

namespace Hotelpay\OpenpaySdk\Src;

use Exception;

class OpenpayException extends Exception {}
class OpenpayConnectionException extends OpenpayException {}
class OpenpayValidationException extends OpenpayException {}
