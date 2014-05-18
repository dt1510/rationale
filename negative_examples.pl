%Hypothesis woman(X):-female(X).
%Induction field
woman(alice).
-woman(alice).
-female(alice).
%Background knowledge
female(alice).
male(john).
%Examples
woman(alice).
%Negative examples
woman(john).

