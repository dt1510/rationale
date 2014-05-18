%Hypothesis woman(X):-female(X).
%Induction field
woman(alice).
-woman(alice).
-female(alice).
%Background knowledge
female(alice).
female(susan).
male(john).
%Examples
woman(alice).
%woman(susan).
%Negative examples
woman(john).

