%Problem adapted to be ground.
%Induction field
-buy(john;diaper).
buy(john;beer).
-buy(john;beer).
shopping(john;at_night).
%Background knowledge
buy(john;diaper):- -buy(john;beer).

%Examples
shopping(john;at_night).
