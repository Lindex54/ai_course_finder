-- Subjects related to each diploma, used to suggest diplomas from a student's subjects.
-- These links are based on programme names and are NOT official admission rules.
-- Staff should review them. Values are comma-separated subject slugs from the subjects table.
-- Safe to re-run.

UPDATE programmes SET related_subjects = 'agriculture,physics,mathematics,chemistry' WHERE code = 'DAG';
UPDATE programmes SET related_subjects = 'agriculture,biology,chemistry' WHERE code = 'DAP';
UPDATE programmes SET related_subjects = 'economics,entrepreneurship,entrepreneurship-education,mathematics,geography' WHERE code = 'DBA';
UPDATE programmes SET related_subjects = 'mathematics,physics,ict,subsidiary-ict' WHERE code = 'DCE';
UPDATE programmes SET related_subjects = 'agriculture,biology,chemistry' WHERE code = 'DCP';
UPDATE programmes SET related_subjects = 'physics,mathematics' WHERE code = 'DEE';
UPDATE programmes SET related_subjects = 'english-language,mathematics,kiswahili,literature-in-english' WHERE code = 'DEP';
UPDATE programmes SET related_subjects = 'physics,mathematics,chemistry' WHERE code = 'DIG';
UPDATE programmes SET related_subjects = 'physics,mathematics' WHERE code = 'DME';
UPDATE programmes SET related_subjects = 'physics,mathematics,geography' WHERE code = 'DNS';
UPDATE programmes SET related_subjects = 'english-language,ict,history,history-and-political-education' WHERE code = 'DRI';
UPDATE programmes SET related_subjects = 'geography,economics,english-language' WHERE code = 'DTT';
UPDATE programmes SET related_subjects = 'biology,chemistry' WHERE code = 'SLB';
UPDATE programmes SET related_subjects = 'chemistry,biology,mathematics' WHERE code = 'SLC';
UPDATE programmes SET related_subjects = 'physics,mathematics' WHERE code = 'SLP';
