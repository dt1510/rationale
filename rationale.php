<?php
/******************************************************************************/
/* Rationale, Inductive Logic Programming system                              */
/* Author: David Toth (dt1510@imperial.ac.uk)                                 */
/* Date:   May 2014                                                           */
/******************************************************************************/
#Theories and produced hypotheses are in cnf, e.g. array(array("a","b"),array("c","-d")).

DEFINE("ARGUMENT_SEPARATOR", ";");
include_once "print.php";
include_once "proving.php";

echo "Rationale, Inductive Logic Programming system\n";

$learning_problem_file=$argv[1];
$content=file_get_contents($learning_problem_file);
$background=get_background_knowledge_formulas($content);
$examples=get_examples_formulas($content);
$negative_examples=get_negative_examples_formulas($content);
$induction_field=get_induction_field($content);
$hypotheses=get_hypotheses($examples, $negative_examples, $background, $induction_field);
echo count($hypotheses)." hypotheses:\n";
print_3dr($hypotheses);

function union($theory1, $theory2) {
    return array_merge($theory1, $theory2);
}

#Each hypothesis is a cnf formula.
function get_hypotheses($examples, $negative_examples, $background, $induction_field) {
    $subsumer=get_hypotheses_subsumer($examples, $background, $induction_field);
    //Need to check the consistency and explanation conditions?
    $hypotheses=antisubsumed_formulas($subsumer);
    $consistent_hypotheses=array();
    foreach($hypotheses as $hypothesis) {
        if(!entails_negative_examples($background, $hypothesis, $negative_examples) && is_consistent(union($background, $hypothesis))) {
            array_push($consistent_hypotheses, $hypothesis);
        }
    }
    return $consistent_hypotheses;
}

#Negative examples are in the dnf form, not cnf.
function entails_negative_examples($background, $hypothesis, $negative_examples) {
    foreach($negative_examples as $negative_example) {
        if(entails_clause(union($background, $hypothesis),$negative_example))
            return true;
    }
    return false;
}

#TODO use system Progol, Clingo, Otter or other theorem prover, otherwise I would need to implement all the algorithms from the scratch myself.
#If I cannot find something quickly, implement it myself. Why not? It is just a binary resolution with mgu.
function entails_clause($cnf, $clause) {
    #print_2dr1($cnf); print_r1($clause); echo "*****\n";
    return false;
}

function prolog_from_cnf($cnf) {
    $prolog="";
    foreach($cnf as $clause) {
        $prolog.=prolog_from_clause($clause)."\n";
    }
    return $prolog;
}

function prolog_from_clause($clause) {
    $prolog="";
        
}

##TODO
#function is_consistent($theory) {
#    return true;    
#}

//Produces the most specific hypothesis.
function get_hypotheses_subsumer($examples, $background, $induction_field) {
    $bridge=get_bridge_formulas($background, $examples);
    $tautologies=generate_tautologies($induction_field);
    echo "Tautologies ";print_2dr1($tautologies);
    echo "Most general bridge theory ";print_2dr1($bridge);    
    $subsumer=prune_duplicate_theory_literals(remove_tautologies(remove_properly_subsumed(complement(union($bridge,$tautologies)))));
    echo "Most specific hypothesis ";print_2dr1($subsumer);
    return $subsumer;
}

function prune_duplicate_theory_literals($theory) {
    foreach($theory as $key=>$clause) {
        $theory[$key]=prune_duplicate_literals($clause);
    }
    return $theory;
}

#Theory in cnf.
function prune_duplicate_theory_clauses($theory) {
    $theory=array_values($theory);
    $n=count($theory);
    for($i=0; $i<$n; $i++) {
        for($j=$i+1; $j<$n; $j++) {
            if(isset($theory[$i]) && isset($theory[$j]) && clauses_equivalent($theory[$i], $theory[$j])) {
                unset($theory[$j]);
            }
        }
    }
    return array_values($theory);
}

function prune_duplicate_cnfs($cnfs) {
    $cnfs=array_values($cnfs);
    $n=count($cnfs);
    for($i=0; $i<$n; $i++) {
        for($j=$i+1; $j<$n; $j++) {
            if(isset($cnfs[$i]) && isset($cnfs[$j]) && cnfs_syntactically_equivalent($cnfs[$i], $cnfs[$j])) {
                unset($cnfs[$j]);
            }
        }
    }
    return array_values($cnfs);
}

function cnf_subsumes($cnf1, $cnf2) {
    foreach($cnf2 as $clause2) {
        foreach($cnf1 as $clause1) {
            if(clause_subsumes($clause1, $clause2)) {
                continue 2;
            }
        }
        return false;
    }
    return true;
}

#FIXME does not handle 
#If after possible rearranging the strings of the formulas are equal.
function cnfs_syntactically_equivalent($cnf1, $cnf2) {
    $cnf1=array_values($cnf1);
    $cnf2=array_values($cnf2);
    for($i=0; $i<count($cnf1); $i++) {
        for($j=0; $j<count($cnf2); $j++) {
            if(clauses_syntactically_equal($cnf1[$i], $cnf2[$j])) {
                continue 2;
            }
        }
        return false;
    }
    for($i=0; $i<count($cnf2); $i++) {
        for($j=0; $j<count($cnf1); $j++) {
            if(clauses_syntactically_equal($cnf2[$i], $cnf1[$j])) {
                continue 2;
            }
        }
        return false;
    }    
    return true;
}
#$cnf0=array(array("p"),array("q","p"));
#$cnf1=array(array("q","p"),array("p"));
#echo cnfs_syntactically_equivalent($cnf0,$cnf1)."\n";
function clauses_syntactically_equal($clause1, $clause2) {
    return array_equal($clause1, $clause2);
}

function array_equal($a, $b) {
    return (is_array($a) && is_array($b) && array_diff($a, $b) === array_diff($b, $a));
}

#$cnf0=array(array("p"),array("q","p"));
#$cnf1=array(array("p"),array("r","p"));
#echo cnfs_equivalent($cnf0, $cnf1)."\n";
function cnfs_equivalent($cnf1, $cnf2) {
    return cnf_subsumes($cnf1, $cnf2) && cnf_subsumes($cnf2, $cnf1);
}

function prune_duplicate_literals($clause) {
    $clause=array_values($clause);
    $n=count($clause);
    for($i=0; $i<$n; $i++) {
        for($j=$i+1;$j<$n; $j++) {
            if(isset($clause[$i])&&isset($clause[$j])&&$clause[$i]==$clause[$j]) {
                unset($clause[$j]);
            }
        }
    }
    return array_values($clause);
}

function antisubsumed_formulas($subsumer) {
    $formulas=antisubsumed_formulas_from_dropping($subsumer);
    $formulas2=array();
    foreach($formulas as $formula) {    
        $formulas2=array_merge($formulas2,antisubsumed_formulas_from_antiinstantiation($formula));
    }
    return prune_duplicate_cnfs($formulas2);
}

function antiinstantiate($formula, $terms) {
    $terms=array_values($terms);
    foreach($terms as $key=>$term)
        $formula=replace_term($formula, $term, "X_$key");
    return $formula;
}

function replace_term($cnf, $term, $replacement) {
    foreach($cnf as $clause_key=>$clause) {
        foreach($clause as $literal_key=>$literal) {
            $clause[$literal_key]=replace_literal_term($literal, $term, $replacement);
        }
        $cnf[$clause_key]=$clause;
    }
    return $cnf;
}

function replace_literal_term($literal, $term, $replacement) {
    $args=array_values(get_arguments($literal));
    if(count($args)==0)
        return $literal;
    $new_literal=get_predicate_with_sign($literal)."(".($args[0]==$term?$replacement:$args[0]);
    for($i=1;$i<count($args);$i++)
        $new_literal.=ARGUMENT_SEPARATOR.($args[$i]==$term?$replacement:$args[$i]);
    $new_literal.=")";
    return $new_literal;
}

function antisubsumed_formulas_from_antiinstantiation($formula) {
    $antisubsumed_formulas=array($formula);
    $all_terms=get_terms_from_cnf($formula);
    $terms_combinations=array_power_set($all_terms);
    foreach($terms_combinations as $terms) {
        array_push($antisubsumed_formulas, antiinstantiate($formula, $terms));
    }
    return prune_duplicate_cnfs($antisubsumed_formulas);
}

function array_power_set($array) {
    $results = array(array( ));
    foreach ($array as $element)
        foreach ($results as $combination)
            array_push($results, array_merge(array($element), $combination));
    return $results;
}

function get_terms_from_cnf($cnf) {
    $terms=array();
    foreach($cnf as $clause) {
        $terms=array_merge($terms, get_terms_from_clause($clause));
    }
    return array_unique($terms);
}

function get_terms_from_clause($clause) {
    $terms=array();
    foreach($clause as $literal) {
        $terms=array_merge($terms, get_terms_from_literal($literal));
    }
    return array_unique($terms);
}

#Constructs formulas by dropping from the initial $formula.
function antisubsumed_formulas_from_dropping($formula) {
    $formula=prune_duplicate_theory_clauses($formula);
    $formulas=array();
    array_push($formulas, $formula);
    foreach($formula as $clause_key=>$clause) {
        if(count($clause)>1) {
            foreach($clause as $literal_key=>$literal) {
                $reduced_clause=$clause;
                unset($reduced_clause[$literal_key]);
                $reduced_formula=$formula;
                $reduced_formula[$clause_key]=$reduced_clause;
                $formulas=array_merge($formulas, antisubsumed_formulas_from_dropping($reduced_formula));
            }
        }
    }
    return prune_duplicate_cnfs($formulas);
}

function generate_tautologies($induction_field) {
    $induction_field=array_values($induction_field);
    $tautologies=array();
    for($i=0; $i<count($induction_field); $i++) {
        for($j=$i+1; $j<count($induction_field); $j++) {
            if($induction_field[$i]==negation($induction_field[$j])) {
                array_push($tautologies, array($induction_field[$i], $induction_field[$j]));
            }
        }
    }
    return $tautologies;
}

function remove_tautologies($theory) {
    foreach($theory as $key=>$clause) {
        if(is_tautology($clause)) {
            unset($theory[$key]);
        }
    }
    return array_values($theory);
}

function is_tautology($clause) {
    $clause=array_values($clause);
    for($i=0; $i<count($clause); $i++) {
        for($j=$i+1; $j<count($clause); $j++) {
            if(instance_of(negation($clause[$i]),$clause[$j]) || instance_of($clause[$j], negation($clause[$i]))) {
                return true;
            }            
        }
    }
    return false;  
}

#Removes the duplicates and properly subsumed clauses within the theory.
function remove_properly_subsumed($theory) {
    $n=count($theory);
    $theory=array_values($theory);
    for($i=0; $i<$n; $i++) {
        for($j=0; $j<$n; $j++) {
            if($i!=$j && isset($theory[$i]) && isset($theory[$j]) && clause_subsumes($theory[$i], $theory[$j])) {
                unset($theory[$j]);
            }
        }
    }
    return array_values($theory);
}

function clauses_equivalent($clause1, $clause2) {
    return clause_subsumes($clause1, $clause2) && clause_subsumes($clause2, $clause1);
}

function clause_properly_subsumes($clause, $subsumed_clause) {
    return clause_subsumes($clause, $subsumed_clause) && !clause_subsumes($subsumed_clause, $clause);
}

#Returns true iff $clause subsumes $subsumed_clause.
function clause_subsumes($clause, $subsumed_clause) {
    #For grounded case: $clause subsumes $subsumed_clause iff $clause subset of $subsumed_clause.
    foreach($clause as $literal) {
        foreach($subsumed_clause as $subsumed_literal) {
            if(instance_of($subsumed_literal, $literal)) {
                goto subsumed_so_far;
            }
        }
        return false;        
        subsumed_so_far:
    }
    return true;
}

#TODO make this work with the ungrounded case too.
function instance_of($subsumed_literal, $literal) {
    return $subsumed_literal==$literal;
}

function get_bridge_formulas($background, $examples) {
    return union($background, complement($examples));
}

abstract class LogicObject {
    const Formula = 0;
    const Literal = 1;
}

function get_literals_between_lines($content,$start_line,$end_line) {
    return get_objects_between_lines($content,$start_line,$end_line,LogicObject::Literal);
}
function get_formulas_between_lines($content,$start_line,$end_line) {
    return get_objects_between_lines($content,$start_line,$end_line,LogicObject::Formula);
}
function get_objects_between_lines($content,$start_line,$end_line,$logic_object) {
    $objects=array();
    $lines=explode("\n",$content);
    $get_object=0;
    foreach($lines as $line) {
        if(strpos($line,$start_line)===0) {            
            $get_object=1;
            continue;
        }        
        if(strpos($line,$end_line)===0) {
            $get_object=0;
        }
        if($get_object==1) {
            if(trim($line)==true) {//skip the empty line.
                if($logic_object==LogicObject::Formula) {
                    array_push($objects,extract_clause($line));
                } else if($logic_object==LogicObject::Literal) {
                    $clause=extract_clause($line);
                    array_push($objects,$clause[0]);
                } else {
                    echo "Error LogicObject::".$logic_object."\n";
                }
            }
        }
    }
    return $objects;
}
function get_induction_field($content) {
    return get_literals_between_lines($content, "%Induction field","%Background knowledge");
}

function get_background_knowledge_formulas($content) {
    return get_formulas_between_lines($content, "%Background knowledge", "%Examples");
}

function get_examples_formulas($content) {
    return get_formulas_between_lines($content, "%Examples", "%Negative examples");
}

function get_negative_examples_formulas($content) {
    return get_formulas_between_lines($content, "%Negative examples", "%EOF");
}

function extract_clause($clause_string) {
    $clause=array();
    $parts=explode(":-",$clause_string);
    $head=$parts[0];
    $body=@$parts[1];
    array_push($clause,extract_literal($head));
    $literal_strings=explode(",",$body);    
    foreach($literal_strings as $literal_string) {
        if(extract_literal($literal_string)==true) {
            array_push($clause,negation(extract_literal($literal_string)));
        }
    }
    return $clause;
}

#$cnf=array(array("a","b"),array("c","-d"));
#TODO support for complement of true, false, empty cnfs.
function complement($cnf) {
    if($cnf===false) {
        return array();//true
    }
    $cnf_complement=array();
    $n=count($cnf);
    if($n==0) {
        #then $cnf is true.
        return false;
    }
    $indices=array_fill(0,$n,0);
    while(true) {        
        $disjunction=array_fill(0,$n,"");
        for($i=0; $i<$n; $i++) {        
            $disjunction[$i]=negation($cnf[$i][$indices[$i]]);
        }
        array_push($cnf_complement,$disjunction);                
        
        //update the counters
        for($counter_index=0; $counter_index<$n; $counter_index++) {
            $indices[$counter_index]++;
            if($indices[$counter_index]>=count($cnf[$counter_index])) {
                if($counter_index==$n-1) {
                    goto end_while;    
                }
                $indices[$counter_index]=0;
                continue;
            }
            break;
        }
    }
    end_while:
    return prune_duplicate_theory_literals($cnf_complement);
}

function negation($literal) {
    if($literal[0]=="-") {
        return substr($literal,1);      
    } else {
        return "-".$literal;
    }
}
?>
