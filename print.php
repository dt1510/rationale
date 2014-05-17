<?php
function print_r1nonl($array) {
    if(!isset($array)) {
        echo "unset";
        return;
    }
    echo "[";
    foreach($array as $value) {
        echo $value." ";
    }
    echo "]";
}
#Prints the array on a single line.
function print_r1($array) {
    print_r1nonl($array);
    echo "\n";
}
#Prints a 2d array on a single line.
function print_2dr1($array2d) {
#    if($array2d==false) {
#        echo "false\n";
#        return;
#    }
#    else if(!is_array($array2d)) {
#        echo $array2d."\n";
#        return;
#    }
    echo "[";
    foreach($array2d as $array1d) {
        echo "[";
        foreach($array1d as $value) {
            echo $value." ";
        }
        echo "]";
    }
    echo "]";
    echo "\n";
}
function print_3dr($array3d) {
    foreach($array3d as $array2d) {
        print_2dr1($array2d);
    }
}
?>
