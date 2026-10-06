-- A-level subject requirements from the official Busitema University
-- "Admission Requirements" document for 2026/2027 (section c, A-level entry).
-- a_level_rules is a JSON list of groups. Each group needs "count" different principal
-- subjects from its "subjects" list. "subjects": null means any A-level subject.
-- a_level_requirement is the official wording, kept for display and checking.
-- Entries for SCE, BEP and DEP use the combination codes on the portal and are not seeded here.
-- DGI in the source document is DIG here. BSY is not in the programme list and is not seeded.
-- Safe to re-run.

UPDATE programmes SET
    a_level_rules = '[{"subjects":["biology","chemistry","agriculture"],"count":2}]',
    a_level_requirement = 'Essential: Two best done of Biology, Chemistry & Agriculture. Relevant: Third best done of the Essential set or one best done of Foods & Nutrition, Economics, Geography, Physics & Mathematics.'
WHERE code = 'APM';

UPDATE programmes SET
    a_level_rules = '[{"subjects":["biology","chemistry","economics","physics","geography","mathematics","agriculture"],"count":2}]',
    a_level_requirement = 'Essential: Two best done of Biology, Chemistry, Economics, Physics, Geography, Mathematics & Agriculture. Relevant: Third best done of the Essential set.'
WHERE code = 'BAB';

UPDATE programmes SET
    a_level_rules = '[{"subjects":["biology","chemistry"],"count":2}]',
    a_level_requirement = 'Essential: Biology and Chemistry. Relevant: One best done of Physics, Agriculture, Geography, Foods and Nutrition, Mathematics and Entrepreneurship.'
WHERE code = 'BSA';

UPDATE programmes SET
    a_level_rules = '[{"subjects":["biology","agriculture"],"count":1}]',
    a_level_requirement = 'Essential: One best done of Biology & Agriculture. Relevant: Second best done of the essential set or Chemistry, Foods & Nutrition.'
WHERE code = 'DAP';

UPDATE programmes SET
    a_level_rules = '[{"subjects":["biology","agriculture"],"count":1}]',
    a_level_requirement = 'Essential: One best done of Biology & Agriculture. Relevant: Second best done of the essential set or Chemistry, Foods & Nutrition.'
WHERE code = 'DCP';

UPDATE programmes SET
    a_level_rules = '[{"subjects":["mathematics","physics"],"count":2}]',
    a_level_requirement = 'Essential: Mathematics & Physics. Relevant: One best done of Chemistry, Biology, Agriculture, Economics & Technical Drawing.'
WHERE code = 'AMI';

UPDATE programmes SET
    a_level_rules = '[{"subjects":["mathematics","physics"],"count":2}]',
    a_level_requirement = 'Essential: Mathematics & Physics. Relevant: One of the best done of Chemistry, Biology, Agriculture, Economics & Technical Drawing.'
WHERE code = 'APE';

UPDATE programmes SET
    a_level_rules = '[{"subjects":["mathematics","physics"],"count":2}]',
    a_level_requirement = 'Essential: Mathematics & Physics. Relevant: One best done of Chemistry, Technical Drawing, Geography and Economics.'
WHERE code = 'BCT';

UPDATE programmes SET
    a_level_rules = '[{"subjects":["mathematics","physics"],"count":2}]',
    a_level_requirement = 'Essential: Mathematics & Physics. Relevant: One best done of Chemistry, Technical Drawing and Economics.'
WHERE code = 'BEE';

UPDATE programmes SET
    a_level_rules = '[{"subjects":["mathematics","physics"],"count":2}]',
    a_level_requirement = 'Essential: Mathematics & Physics. Relevant: Chemistry.'
WHERE code = 'BEM';

UPDATE programmes SET
    a_level_rules = '[{"subjects":["mathematics","physics"],"count":1}]',
    a_level_requirement = 'Essential: One better done of Mathematics & Physics. Relevant: Next better done of Mathematics and Physics.'
WHERE code = 'DAG';

UPDATE programmes SET
    a_level_rules = '[{"subjects":["mathematics","physics"],"count":1}]',
    a_level_requirement = 'Essential: One better done of Mathematics & Physics. Relevant: Chemistry and next better done of Mathematics and Physics.'
WHERE code = 'DEE';

UPDATE programmes SET
    a_level_rules = '[{"subjects":["mathematics","physics","chemistry"],"count":2}]',
    a_level_requirement = 'Essential: Two best done of Mathematics, Physics & Chemistry. Relevant: Third best done of the essential set.'
WHERE code = 'DME';

UPDATE programmes SET
    a_level_rules = '[{"subjects":["mathematics","physics"],"count":1}]',
    a_level_requirement = 'Essential: One better done of Mathematics & Physics. Relevant: Chemistry and next better done of Mathematics and Physics.'
WHERE code = 'DIG';

UPDATE programmes SET
    a_level_rules = '[{"subjects":["mathematics","physics","chemistry"],"count":2}]',
    a_level_requirement = 'Essential: Two best done of Mathematics, Physics & Chemistry. Relevant: Third best done of the essential set.'
WHERE code = 'DNS';

UPDATE programmes SET
    a_level_rules = '[{"subjects":["mathematics","physics","chemistry"],"count":2}]',
    a_level_requirement = 'Essential: Two best done of Mathematics, Physics & Chemistry. Relevant: Third best done of the essential set.'
WHERE code = 'MAE';

UPDATE programmes SET
    a_level_rules = '[{"subjects":["mathematics","physics"],"count":2}]',
    a_level_requirement = 'Essential: Mathematics & Physics. Relevant: One best done of Chemistry & Geography.'
WHERE code = 'MEB';

UPDATE programmes SET
    a_level_rules = '[{"subjects":["mathematics","physics","chemistry"],"count":2}]',
    a_level_requirement = 'Essential: Two best done of Mathematics, Physics & Chemistry. Relevant: Third best done of the essential set or one better done of Geography & Economics.'
WHERE code = 'TEB';

UPDATE programmes SET
    a_level_rules = '[{"subjects":["mathematics","physics","chemistry"],"count":2}]',
    a_level_requirement = 'Essential: Two best done of Mathematics, Physics & Chemistry. Relevant: Third best done of the essential set & Geography, Economics & Biology.'
WHERE code = 'WAR';

UPDATE programmes SET
    a_level_rules = '[{"subjects":["biology","chemistry"],"count":2}]',
    a_level_requirement = 'Essential: Biology & Chemistry. Relevant: Mathematics or Physics.'
WHERE code = 'BNA';

UPDATE programmes SET
    a_level_rules = '[{"subjects":["biology","chemistry"],"count":2}]',
    a_level_requirement = 'Essential: Biology & Chemistry. Relevant: One best done of Math, Physics, Food & Nutrition, Economics and Agriculture.'
WHERE code = 'BNS';

UPDATE programmes SET
    a_level_rules = '[{"subjects":["biology","chemistry"],"count":2}]',
    a_level_requirement = 'Essential: Biology & Chemistry. Relevant: Mathematics or Physics.'
WHERE code = 'MED';

UPDATE programmes SET
    a_level_rules = '[{"subjects":["biology","chemistry"],"count":2}]',
    a_level_requirement = 'Essential: Biology & Chemistry. Relevant: Mathematics or Physics.'
WHERE code = 'PHA';

UPDATE programmes SET
    a_level_rules = '[{"subjects":["mathematics"],"count":1},{"subjects":["physics","economics","entrepreneurship-education"],"count":1}]',
    a_level_requirement = 'Essential: Mathematics and one best done of Physics, Economic and Entrepreneurship. Relevant: Second best done of Physics, Economic, Entrepreneurship, Geography and Chemistry.'
WHERE code = 'BSS';

UPDATE programmes SET
    a_level_rules = '[{"subjects":null,"count":2}]',
    a_level_requirement = 'Essential: Two best done of all A level subjects. Relevant: Third best done of the remaining A Level subjects.'
WHERE code = 'BTI';

UPDATE programmes SET
    a_level_rules = '[{"subjects":["literature-in-english"],"count":1},{"subjects":null,"count":1}]',
    a_level_requirement = 'Essential: Literature in English and one best done of all A-level subjects. Relevant: The third best done of all A-level subjects.'
WHERE code = 'ELS';

UPDATE programmes SET
    a_level_rules = '[{"subjects":["mathematics","physics","agriculture","economics","entrepreneurship-education","geography","chemistry","biology"],"count":2}]',
    a_level_requirement = 'Essential: Two best done of Mathematics, Physics, Agriculture, Economics, Entrepreneurship, Geography, Chemistry & Biology. Relevant: Third best done of the essential set.'
WHERE code = 'SCE';

UPDATE programmes SET
    a_level_rules = '[{"subjects":["mathematics","physics"],"count":2}]',
    a_level_requirement = 'Essential: Mathematics & Physics. Relevant: Chemistry or Economics.'
WHERE code = 'SCS';

UPDATE programmes SET
    a_level_rules = '[{"subjects":["biology"],"count":1}]',
    a_level_requirement = 'Essential: Biology. Relevant: Two better done of Chemistry, Mathematics, Physics and Agriculture.'
WHERE code = 'SLB';

UPDATE programmes SET
    a_level_rules = '[{"subjects":["chemistry"],"count":1}]',
    a_level_requirement = 'Essential: Chemistry. Relevant: Two better done of Biology, Mathematics, Physics and Agriculture.'
WHERE code = 'SLC';

UPDATE programmes SET
    a_level_rules = '[{"subjects":["physics"],"count":1}]',
    a_level_requirement = 'Essential: Physics. Relevant: Two better done of Biology, Mathematics, Chemistry and Agriculture.'
WHERE code = 'SLP';

UPDATE programmes SET
    a_level_rules = '[{"subjects":["agriculture","chemistry","biology"],"count":2}]',
    a_level_requirement = 'Essential: Two best done of Agriculture, Chemistry & Biology. Relevant: One better done of Mathematics, Agriculture, Chemistry, Physics, Biology & Chemistry.'
WHERE code = 'FWR';

UPDATE programmes SET
    a_level_rules = '[{"subjects":["economics","biology","mathematics","agriculture","chemistry","physics","geography"],"count":2}]',
    a_level_requirement = 'Essential: Two best done of Economics, Biology, Mathematics, Agriculture, Chemistry, Physics and Geography. Relevant: Third best done of the same subjects.'
WHERE code = 'NRE';

UPDATE programmes SET
    a_level_rules = '[{"subjects":null,"count":2}]',
    a_level_requirement = 'Essential: Two best done of all A level subjects. Relevant: Third best done of the remaining A Level subjects.'
WHERE code = 'BBA / BBW';

UPDATE programmes SET
    a_level_rules = '[{"subjects":null,"count":2}]',
    a_level_requirement = 'Essential: Two best done of all A level subjects. Relevant: Third best done of the remaining A Level subjects.'
WHERE code = 'BPA';

UPDATE programmes SET
    a_level_rules = '[{"subjects":null,"count":2}]',
    a_level_requirement = 'Essential: Two best done of all A level subjects. Relevant: Third best done of the remaining A Level subjects.'
WHERE code = 'BTT';

UPDATE programmes SET
    a_level_rules = '[{"subjects":["economics"],"count":1},{"subjects":["mathematics"],"count":1}]',
    a_level_requirement = 'Essential: Economics and Mathematics. Relevant: One best done of Geography, Physics and Entrepreneurship.'
WHERE code = 'BPM';

UPDATE programmes SET
    a_level_rules = '[{"subjects":null,"count":1}]',
    a_level_requirement = 'Essential: One best done of all A level subjects. Relevant: Second best done of the remaining A Level subjects.'
WHERE code = 'DBA';

UPDATE programmes SET
    a_level_rules = '[{"subjects":null,"count":1}]',
    a_level_requirement = 'Essential: One best done of all A level subjects. Relevant: Second best done of the remaining A Level subjects.'
WHERE code = 'DRI';

UPDATE programmes SET
    a_level_rules = '[{"subjects":null,"count":1}]',
    a_level_requirement = 'Essential: One best done of all A level subjects. Relevant: Second best done of the remaining A Level subjects.'
WHERE code = 'DTT';

UPDATE programmes SET
    a_level_rules = '[{"subjects":["economics","mathematics"],"count":1},{"subjects":null,"count":1}]',
    a_level_requirement = 'Essential: Economics or Mathematics and one best done of the remaining A level subjects. Relevant: Best done of the remaining A level subjects.'
WHERE code = 'ENM';

-- Diploma in Computer Engineering: the A-level entry route in the same document.
UPDATE programmes SET
    a_level_rules = '[{"subjects":["mathematics","physics"],"count":1}]',
    a_level_requirement = 'A holder of a Uganda Certificate of Education with at least a principal pass in Mathematics or Physics at the Uganda Advanced Certificate of Education level.'
WHERE code = 'DCE';

-- Education Primary: the document lists A-level combination codes for these two programmes
-- (see the portal). The subject combinations are not seeded, so the rule only records that
-- an A-level route exists. An empty rule list means no subject condition.
UPDATE programmes SET
    a_level_rules = '[]',
    a_level_requirement = 'Applicants use the specific subject combination codes shown on the application portal.'
WHERE code IN ('DEP', 'BEP');
