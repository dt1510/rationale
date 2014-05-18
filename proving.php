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
    $is_negative=is_negative($literal);
    $lbracket=leftmost_pos($literal,"(");
    return $lbracket==-1 ? substr($literal,$is_negative) : substr($literal,$is_negative,$lbracket-$is_negative);
}

function is_negative($literal) {
    return $literal[0]=="-";
}

function get_args($literal) {
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
    return array_unique(get_args($literal));
}

function extract_literal($literal_string) {
    return trim($literal_string, " \t\n\r\0\x0B.");
}

#TODO make this work with the function symbols
function get_vars($arg) {
    $variables=array();
    if(ctype_upper($arg[0])) {
        array_push($variables, $arg);
    }
    return $variables;
}


function get_clause_vars($clause) {
    $vars=array();
    foreach($clause as $literal) {
        $vars=array_merge($vars,$literal->get_vars());
    }
    return $vars;
}

function clause_apply_substitution($clause, $theta) {
    foreach($clause as $key=>$literal) {
        $clause[$key]->apply_substitution($theta);
    }
    return $clause;
}

#$clause=array(new Literal("p(X;X2;a)"), new Literal("q(b;Y;X)"));
#print_clause($clause);
#$theta=array();$theta["X"]="Y2";$theta["X2"]="c";$theta["Test"]="abraca";
#$clause=clause_apply_substitution($clause, $theta);
#print_clause(standarize_apart_clause($clause,array("X","Y")));

#Returns a clause whose variables do not intersect with $vars.
function standarize_apart_clause($clause, $vars) {
    $clause_vars=get_clause_vars($clause);
    $theta=standarize_apart_vars($clause_vars,$vars);
    return clause_apply_substitution($clause, $theta); 
}

class Literal {
    public $negative=false;
    public $arguments=array();
    public $predicate;
    public function __construct($literal_string) {
        $literal_string=extract_literal($literal_string);
        $this->arguments=get_args($literal_string);
        $this->predicate=get_predicate($literal_string);
        $this->negative=is_negative($literal_string);         
    }
    
    public function is_negative() {
        return $this->negative;
    }
    
    public function get_predicate() {
        return $this->predicate;
    }
    
    public function get_predicate_arity() {
        return count($this->arguments);
    }
    
    public function get_args() {
        return $this->arguments;
    }

    #TODO make this work with the function symbols
    public function replace_var($var, $replacement) {               
        foreach($this->arguments as $key=>$arg) {
            if($arg==$var) {
                $this->arguments[$key]=$replacement;
            }
        }
    }
    
    public function apply_substitution($theta) {
        foreach($theta as $var=>$replacement) {
            $this->replace_var($var, $replacement);
        }
    }

    #Finds the substitution for the $this Literal to be applied to standarize it apart from the $variables.
#    public function standarize_apart_substitution($variables) {
#        
#    }

#    public function standarize_apart($literal) {
#        $literal->get_vars();
#        $vars=$this->get_vars();
#        $vars2=$literal->get_vars();
#        $all_vars=array_values(array_unique(array_merge($vars,$vars2)));
#        $common_vars=array_intersect($vars,$vars2);
#        $new_literal=clone $this;
#        foreach($common_vars as $var) {
#            $different_var=generate_different_var($all_vars);
#            $new_literal->replace_var($var, $different_var);
#            array_push($all_vars, $different_var);
#        }
#        return $new_literal;
#    }
    
    public function to_string() {
        $string=($this->is_negative()?"-":"").($this->predicate);
        if($this->get_predicate_arity()>0) {
        $string.="(";
        $has_args=false; 
        foreach($this->get_args() as $arg) {
            $string.=($has_args?ARGUMENT_SEPARATOR:"");
            $string.=$arg;
            $has_args=true;
        }
        $string.=")";
        }
        return $string;
    }
    
    public function get_vars() {
        $variables=array();
        foreach($this->arguments as $argument) {
            $variables=array_merge($variables, get_vars($argument));
        }
        return array_values(array_unique($variables));
    }
}

#Returns a substitution to be applied on the $vars to standarize them apart from the variables $var2.
function standarize_apart_vars($vars, $vars2) {
    $substitution=array();
    $all_vars=array_values(array_unique(array_merge($vars,$vars2)));
    $common_vars=array_intersect($vars,$vars2);
    foreach($common_vars as $var) {
        $different_var=generate_different_var($all_vars);
        $substitution[$var]=$different_var;
        array_push($all_vars, $different_var);
    }
    return $substitution;
}

#$vars=array("X1", "X2", "X3","X5");
#$vars2=array("X1", "Y3", "X2");
#$theta=standarize_apart_vars($vars, $vars2);
#print_r1k($theta);

#$l1=new Literal("p(X0; X1; a; X3)");
#$l2=new Literal("p(X0; X1; a; X5)");
#echo $l1->to_string()."\n";
#echo $l2->to_string()."\n";
#$l3=$l1->standarize_apart($l2);
#echo $l3->to_string()."\n";
#echo "-------------\n";

#Generates a variable name that does not appear on the list.
function generate_different_var($vars) {
    $index=0;
    while(true) {
        $index++;
        foreach($vars as $var) {
            if($var=="X$index")
                continue 2;
        }
        return "X$index";
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

function is_empty_clause($clause) {
    return count($clause)==0;
}

function is_var($arg) {
    return strlen($arg)>0 && ctype_upper($arg[0]);
}

function is_term($arg) {
    return strlen($arg)>0 && !ctype_upper($arg[0]);
}

#TODO case with all args vars
#TODO function symbols support.
#Finds the mgu for the args with the same indices in the arrays.
function mgu_args($args, $args2) {
    $mgu=array();
    $args=array_values($args);
    $args2=array_values($args2);
    $size=max(count($args),count($args2));
    for($i=0; $i<$size; $i++) {
        if(is_var($args[$i]) && is_var($args2[$i])) {
            $var=$args[$i];
            $replacement=$args2[$i];            
        } else if(is_var($args[$i]) && is_term($args2[$i])) {
            $var=$args[$i];
            $replacement=$args2[$i];
        } else if(is_term($args[$i]) && is_var($args2[$i])) {
            $var=$args2[$i];
            $replacement=$args[$i];
        } else if(is_term($args[$i]) && is_term($args2[$i])) {            
            if($args[$i]!=$args2[$i])
                return false;
            continue;            
        }

        $mgu[$var]=$replacement;
        foreach($args as $key=>$arg)
            $args[$key]=replace_var($args[$key], $var, $replacement);
        foreach($args2 as $key2=>$arg2)
            $args2[$key2]=replace_var($args2[$key2], $var, $replacement);
    }
    return $mgu;
}

#$args=array("X1","X2","a");
#$args2=array("Y1","a","Y2");
#print_r1k(mgu_args($args,$args2));

#Replaces a variable in an argument.
#TODO function symbol support.
function replace_var($arg, $var, $replacement) {
    return ($arg==$var)?$replacement:$arg;
}

#TODO make this work with the function symbols.
#Returns the mgu if the clauses can be resolved.
function resolvement_mgu($literal, $literal2) {
    if($literal->get_predicate()!=$literal2->get_predicate())
        return false;
    if(!(((bool)$literal->is_negative()) ^ ((bool)$literal2->is_negative())))
        return false;
    return mgu_args($literal->get_args(), $literal2->get_args());
}

function mgu($literal, $literal2) {
    if(compatible_literals($literal, $literal2))
        return mgu_args($literal->get_args(), $literal2->get_args());
    return false;
}

function compatible_literals($literal, $literal2) {
    if($literal->get_predicate()!=$literal2->get_predicate())
        return false;
    if((((bool)$literal->is_negative()) ^ ((bool)$literal2->is_negative())))
        return false;
    return true;
}

function clause_copy($clause) {
    $clause_copy=array();
    foreach($clause as $key=>$literal) {
        $clause_copy[$key]=clone $literal;
    }
    return $clause_copy;
}

#$l1=new Literal("p(X1;X2;a)");
#$l2=new Literal("p(X2;a;X3)");
#$mgu=resolvement_mgu($l1,$l2);
#print_r1k($mgu);

#$clause=array(new Literal("q(X;a)"), new Literal("-q(X;X2)"));
#$clause2=array(new Literal("-q(a;Y)"), new Literal("q(Y;X2)"));
#$resolvements=get_resolvements($clause,$clause2);
#foreach($resolvements as $clause) {
#    print_clause($clause);
#}

#TODO
#A clause here is a set of the Literal objects.
function get_resolvements($clause, $clause2) {
    $resolvements=array();
    $vars=get_clause_vars($clause);    
    $clause2=standarize_apart_clause($clause2, $vars);    
    foreach($clause as $key=>$literal) {
        foreach($clause2 as $key2=>$literal2) {                        
            $theta=resolvement_mgu($literal,$literal2);
            if(is_array($theta)) {
                $clause_copy=clause_copy($clause);
                $clause2_copy=clause_copy($clause2);
                unset($clause_copy[$key]);
                unset($clause2_copy[$key2]);                
                $resolvement=clause_apply_substitution(array_merge($clause_copy, $clause2_copy),$theta);
                #print_clause_nonl($clause);print_clause_nonl($clause2);echo " -> ";print_clause_nonl($resolvement);echo "\n";
                array_push($resolvements, $resolvement);
            }
        }        
    }
    
    return $resolvements;
}

?>
