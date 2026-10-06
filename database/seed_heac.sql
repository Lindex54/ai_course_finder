-- Higher Education Access Certificate (HEAC) combinations from the official
-- 2026/2027 "Admission Requirements" document (section c, certificate programmes).
-- Each certificate needs both listed subjects, taken at A-level.
-- HEG and HHE are in the call's list of combinations, but their wording was not
-- extracted from the document, so they are marked for checking.
-- Safe to re-run.

UPDATE programmes SET
    a_level_rules = '[{"subjects":["biology"],"count":1},{"subjects":["agriculture"],"count":1}]',
    a_level_requirement = 'Essential: Biology & Agriculture. Desirable: General Paper & Sub-Maths or Computer Studies.'
WHERE code = 'HBA';

UPDATE programmes SET
    a_level_rules = '[{"subjects":["biology"],"count":1},{"subjects":["chemistry"],"count":1}]',
    a_level_requirement = 'Essential: Biology & Chemistry. Desirable: General Paper & Sub-Maths or Computer Studies.'
WHERE code = 'HBC';

UPDATE programmes SET
    a_level_rules = '[{"subjects":["mathematics"],"count":1},{"subjects":["chemistry"],"count":1}]',
    a_level_requirement = 'Essential: Mathematics & Chemistry. Desirable: General Paper & Sub-Maths or Computer Studies.'
WHERE code = 'HMC';

UPDATE programmes SET
    a_level_rules = '[{"subjects":["physics"],"count":1},{"subjects":["mathematics"],"count":1}]',
    a_level_requirement = 'Essential: Physics & Mathematics. Desirable: General Paper & Sub-Maths or Computer Studies.'
WHERE code = 'HPM';

UPDATE programmes SET
    a_level_rules = '[{"subjects":["english-language"],"count":1},{"subjects":["literature-in-english"],"count":1}]',
    a_level_requirement = 'Essential: English Language & Literature in English. Desirable: General Paper & Sub-Maths or Computer Studies.'
WHERE code = 'HEL';

UPDATE programmes SET
    a_level_rules = '[{"subjects":["history"],"count":1},{"subjects":["economics"],"count":1}]',
    a_level_requirement = 'Combination History & Economics, as listed in the 2026/2027 call. Detailed essential subjects to be checked with the Academic Registrar.'
WHERE code = 'HHE';

UPDATE programmes SET
    a_level_rules = '[{"subjects":["economics"],"count":1},{"subjects":["geography"],"count":1}]',
    a_level_requirement = 'Combination Economics & Geography. Detailed essential subjects to be checked with the Academic Registrar.'
WHERE code = 'HEG';
