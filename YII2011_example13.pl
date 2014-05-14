%Example 13
%Hypothesis induced(hxt):- active(snf3), glycolysis_on:- induced(hxt).
%Induction field
-active(snf3).
induced(hxt).
-induced(hxt).
glycolysis_on.

%Background knowledge
induced(hxt):- glucose_ext, -active(snf3).

%Examples
glycolysis_on:-glucose_ext.

