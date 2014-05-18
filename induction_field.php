<?php

include_once "proving.php";

#Generalizes the $cnf theory by removing the literals whose ground instances not in the induction_field.
#This procedure preserves the completeness.
function antisubsume_by_induction_field($cnf, $induction_field_objects) {
    foreach($cnf as $clause_key=>$clause) {
        foreach($clause as $literal_key=>$literal) {
            if(!grounded_in_induction_field(array(new Literal($literal)), $induction_field_objects)) {
                unset($cnf[$clause_key][$literal_key]);
            }
        }
    }
    return $cnf;
}

#$induction_field=array(new Literal("s(X)"), new Literal("-s(Y)"), new Literal("p(c,X)"));
#$cnf=array(array("s(a)","s(b)","-s(X)","p(d,d)"));
#print_2dr1(antisubsume_by_induction_field($cnf,$induction_field));

#TODO function support.
function get_ground_instances_from_literal($literal_string) {
    $ground_instances=array();
    foreach(get_args($literal_string) as $arg) {
        if(is_term($arg)) {
            array_push($ground_instances, $arg);
        }
    }
    return $ground_instances;
}

#$cnf=array(array("p(a)","p(b)","s(X;c)"),array("r(d;e)","-t(f;X;g)"));
#print_r1(get_domain($cnf));

#Returns the ground instances from the cnf or dnf theory.
function get_domain($theory) {
    $ground_instances=array();
    foreach($theory as $formula) {
        foreach($formula as $literal) {
            $ground_instances=array_merge($ground_instances, get_ground_instances_from_literal($literal));
        }
    }
    return $ground_instances;
}

#A cnf is in an induction field iff it has a ground instance whose literals are in the induction field.
#$induction_field_objects consists of Literal objects.
#$domain is a subset of the Herband universe.
function in_induction_field($cnf, $induction_field_objects) {
    return grounded_in_induction_field(literals_from_cnf($cnf), $induction_field_objects);    
}

#Returns true iff there exists a substitution theta such that $literals theta subset of $induction_field.
function grounded_in_induction_field($literals, $induction_field) {
    if(count($literals)==0)
        return true;
    $literal=array_pop($literals);
    foreach($induction_field as $induction_field_literal) {
        $mgu=mgu($literal,$induction_field_literal);
        if(is_array($mgu)) {            
            if(grounded_in_induction_field(substituted_literals($literals, $mgu), $induction_field))
                return true;                
        }
    }    
    return false;
}

#Applies a substitution theta to all the copies of the literals in the list.
function substituted_literals($literals, $theta) {
    $substituted_literals=array();
    foreach($literals as $literal) {
        $literal_clone=clone $literal;
        $literal_clone->apply_substitution($theta);
        array_push($substituted_literals, $literal_clone);
    }
    return $substituted_literals;
}

#$literals=array(new Literal("p"), new Literal("s(X)"), new Literal("pr(Y1;X)"));
#$induction_field=array(new Literal("p"), new Literal("s(a)"), new Literal("pr(a;b)"), new Literal("s(b)"));
#$theta=array();
#$theta["X"]="b";
#$theta["Y"]="X4";
#echo "grounded_in_induction_field ".grounded_in_induction_field($literals, $induction_field)."\n";

function literals_from_cnf($cnf) {
    $literals=array();
    foreach($cnf as $clause) {
        foreach($clause as $literal) {
            array_push($literals, $literal);
        }
    }
    $literals=array_unique($literals);
    return literal_objects($literals);
}

function literal_objects($literals) {
    $literal_objects=array();
    foreach($literals as $literal) {
        array_push($literal_objects, new Literal($literal));
    }
    return $literal_objects;
}

?>
