<?php

#Returns the position of the leftmost occurance of $char in $string, or -1 if $char not in $string.
function leftmost_pos($string, $char) {
    for($i=0; $i<strlen($string); $i++) {
        if($string[$i]==$char) {
            return $i;
        }
    }
    return -1;
}

function rightmost_pos($string, $char) {
    for($i=strlen($string)-1; $i>=0; $i--) {
        if($string[$i]==$char) {
            return $i;
        }
    }
    return -1;    
}

function get_predicate($literal) {
    $lbracket=leftmost_pos($literal,"(");
    return $lbracket==-1 ? $literal : substr($literal,0,$lbracket);
}

function get_arguments($literal) {
    $lbracket=leftmost_pos($literal,"(");
    $rbracket=rightmost_pos($literal,")");
    if($lbracket==-1 || $rbracket==-1 || $lbracket>$rbracket) {
        return array();
    }
    $arg_body=substr($literal,$lbracket+1,$rbracket-$lbracket-1);
    $terms=explode(ARGUMENT_SEPARATOR,$arg_body);
    foreach($terms as $key=>$term) {
        $terms[$key]=trim($term);
    }
    return $terms;
}

#TODO make it work with the function symbols.
function get_terms_from_literal($literal) {
    return array_unique(get_arguments($literal));
}

function extract_literal($literal_string) {
    return trim($literal_string, " \t\n\r\0\x0B.");
}

class Literal {
    public $negated=false;
    public $arguments=array();
    public $predicate;
    public function __construct($literal_string) {
        
    }
}

?>
