%Example 12
%Hypothesis path(X,Y):-arc(b,c),arc(X,Y)
%Induction field
arc(b,c).
-arc(b,c).
path(b,c).
-path(b,c).

%Background knowledge
arc(a,b).
path(X,Z):-path(X,Y), path(Y,Z).

%Examples
path(a,c).
