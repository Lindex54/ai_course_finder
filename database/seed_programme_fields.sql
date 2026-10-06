-- Field of study for programmes, used to make sure IT students see computing programmes.
-- Covers bachelor's degrees and diplomas only. Safe to re-run.

UPDATE programmes SET field_of_study = 'Computing'
WHERE code IN ('BCT', 'BTI', 'SCS', 'DCE');
