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

#Returns a predicate symbol with the negation if present.
function get_predicate_with_sign($literal) {
    $lbracket=leftmost_pos($literal,"(");
    return $lbracket==-1 ? $literal : substr($literal,0,$lbracket);
}

function get_predicate($literal) {
    $lbracket=leftmost_pos($literal,"(");
    return $lbracket==-1 ? $literal : substr($literal,is_negative($literal),$lbracket);
}

function is_negative($literal) {
    return $literal[0]=="-";
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
    public $negative=false;
    public $arguments=array();
    public $predicate;
    public function __construct($literal_string) {
        $literal_string=extract_literal($literal_string);
        $arguments=array();
        $arguments=get_arguments($literal_string);
        $predicate=get_predicate($literal_string);
        $negative=is_negative($literal_string);         
    }
    
    public function is_negative() {
        return $negative;
    }
}

#Converts an array of the clauses of the literal strings to an array of clauses of Literal objects.
function cnf_to_object_cnf($cnf) {
    $object_cnf=array();
    foreach($cnf as $clause) {
        $object_clause=array();
        foreach($clause as $literal_string) {
            array_push($object_clause, new Literal($literal_string));
        }
        array_push($object_cnf, $object_clause);
    }
    return $object_cnf;
}

function is_consistent($cnf) {
    return !finds_refutation(cnf_to_object_cnf($cnf));
}

#Returns true iff it can find an empty clause by the resolution from the clauses.
#A clause here is an array of Literal objects, not literal_strings.
function finds_refutation($clauses) {
    #Performs the satured search and the binary resolution.
    $level=0;
    $clause_levels=array();
    $clause_levels[0]=$clauses;
    while(count($clause_levels[$level])>0) {
        $next_level=count($clause_levels);
        $clause_levels[$next_level]=array();
        #Resolve all the clauses of the current level with the clauses in the other levels except the new added level $clause_levels[$next_level].
        foreach($clause_levels[$level] as $clause) {
            for($i=0; $i<=$level; $i++) {
                foreach($clause_levels[$i] as $clause2) {
                    $resolvements=get_resolvements($clause, $clause2);
                    foreach($resolvements as $clause3) {
                        if(is_empty_clause($clause3))
                            return true;
                    }
                    $clause_levels[$next_level]=array_merge($clause_levels[$next_level], $resolvements);
                }
            }
        }
        $level=$next_level;
    }
    return false;
}

#TODO
function is_empty_clause($clause) {
    return false;
}

#TODO
#Returns the mgu if the clauses can be resolved.
function resolvement_mgu($literal, $literal2) {
    //return $literal.
    return false;
}

#TODO
#Applies the substitution $substitution to the $literal.
function substituted_literal($literal, $substitution) {
    return false;
}

#TODO
#A clause here is a set of the Literal objects.
function get_resolvements($clause, $clause2) {
    $resolvements=array();
    foreach($clause as $key=>$literal) {
        foreach($clause2 as $key2=>$literal2) {
            $theta=resolvement_mgu($literal,$literal2);
            if($theta) {
                $clause_copy=$clause;
                $clause2_copy=$clause2;
                unset($clause_copy[$key]);
                unset($clause2_copy[$key2]);
                $resolvement=array_merge($clause_copy, $clause2_copy);
                array_push($resolvement, substituted_literal($literal, $theta));
                array_push($resolvements, $resolvement);
            }
        }        
    }
    
    return $resolvements;
}

?>
