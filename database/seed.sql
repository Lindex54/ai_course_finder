-- Busitema University programme catalogue for the 2026/2027 admissions cycle.
-- Source: Busitema_Programme_Catalogue_and_AI_Finder_Plan_2026_2027.docx
-- Faculty assignments are intentionally left unverified because the source
-- document does not provide authoritative programme-to-faculty mappings.

START TRANSACTION;

INSERT INTO faculties (name, code, description, status)
VALUES (
    'Unassigned / To Be Verified',
    NULL,
    'Temporary catalogue assignment for programmes whose faculty was not verified in the supplied 2026/2027 source.',
    'active'
)
ON DUPLICATE KEY UPDATE
    name = VALUES(name);

SET @catalogue_faculty_id := (
    SELECT id
    FROM faculties
    WHERE name = 'Unassigned / To Be Verified'
    LIMIT 1
);

INSERT INTO programmes (
    faculty_id,
    name,
    code,
    slug,
    duration,
    award_type,
    status
)
VALUES
    -- Doctoral programmes
    (@catalogue_faculty_id, 'PhD in Business Administration and Management', 'PAM', 'pam', 3.0, 'PhD', 'active'),
    (@catalogue_faculty_id, 'PhD in Biodiversity Conservation and Management', 'PBC', 'pbc', 3.0, 'PhD', 'active'),
    (@catalogue_faculty_id, 'PhD in Chemistry', 'PCH', 'pch', 3.0, 'PhD', 'active'),
    (@catalogue_faculty_id, 'PhD in Cyber Systems', 'PCP', 'pcp', 3.0, 'PhD', 'active'),
    (@catalogue_faculty_id, 'PhD in Energy Engineering', 'PEE', 'pee', 3.0, 'PhD', 'active'),
    (@catalogue_faculty_id, 'PhD in Education Leadership and Management', 'PEL', 'pel', 3.0, 'PhD', 'active'),
    (@catalogue_faculty_id, 'PhD in Education Psychology', 'PEP', 'pep', 3.0, 'PhD', 'active'),
    (@catalogue_faculty_id, 'PhD in Natural Resource and Environmental Sciences', 'PES', 'pes', 3.0, 'PhD', 'active'),
    (@catalogue_faculty_id, 'PhD of Science in Global Change and Sustainable Agriculture', 'PGA', 'pga', 3.0, 'PhD', 'active'),
    (@catalogue_faculty_id, 'PhD in Materials Engineering', 'PME', 'pme', 3.0, 'PhD', 'active'),
    (@catalogue_faculty_id, 'PhD in Physics', 'PSP', 'psp', 3.0, 'PhD', 'active'),

    -- Master's programmes
    (@catalogue_faculty_id, 'Master of Science in Cyber Physical Systems Engineering', 'CPS', 'cps', 2.0, 'Master''s Degree', 'active'),
    (@catalogue_faculty_id, 'Master of Educational Leadership and Management', 'EDM', 'edm', 2.0, 'Master''s Degree', 'active'),
    (@catalogue_faculty_id, 'Master of Science in Infectious Diseases Field Epidemiology', 'IDE', 'ide', 2.0, 'Master''s Degree', 'active'),
    (@catalogue_faculty_id, 'Master of Science in Artificial Intelligence', 'MAI', 'mai', 2.0, 'Master''s Degree', 'active'),
    (@catalogue_faculty_id, 'Master of Business Administration', 'MBA', 'mba', 2.0, 'Master''s Degree', 'active'),
    (@catalogue_faculty_id, 'Master of Science in Climate Change and Disaster Management', 'MCC', 'mcc', 2.0, 'Master''s Degree', 'active'),
    (@catalogue_faculty_id, 'Master of Computer Forensics', 'MCF', 'mcf', 2.0, 'Master''s Degree', 'active'),
    (@catalogue_faculty_id, 'Master of Science in Chemistry', 'MCH', 'mch', 2.0, 'Master''s Degree', 'active'),
    (@catalogue_faculty_id, 'Master of Science in Environmental Economics', 'MEE', 'mee', 2.0, 'Master''s Degree', 'active'),
    (@catalogue_faculty_id, 'Masters in Educational Psychology', 'MEP', 'mep', 2.0, 'Master''s Degree', 'active'),
    (@catalogue_faculty_id, 'Master of Science in Global Change and Sustainable Agriculture', 'MGA', 'mga', 2.0, 'Master''s Degree', 'active'),
    (@catalogue_faculty_id, 'Master of Science in Irrigation and Drainage Engineering', 'MID', 'mid', 2.0, 'Master''s Degree', 'active'),
    (@catalogue_faculty_id, 'Master of Science in Industrial Mathematics', 'MIM', 'mim', 2.0, 'Master''s Degree', 'active'),
    (@catalogue_faculty_id, 'Master of Science in Materials Engineering', 'MME', 'mme', 2.0, 'Master''s Degree', 'active'),
    (@catalogue_faculty_id, 'Master of Medicine, Internal Medicine', 'MMM', 'mmm', 3.0, 'Master''s Degree', 'active'),
    (@catalogue_faculty_id, 'Master of Medicine in Obstetrics and Gynecology', 'MOG', 'mog', 3.0, 'Master''s Degree', 'active'),
    (@catalogue_faculty_id, 'Masters in Public Administration', 'MPA', 'mpa', 2.0, 'Master''s Degree', 'active'),
    (@catalogue_faculty_id, 'Master of Science in Plant Breeding', 'MPB', 'mpb', 2.0, 'Master''s Degree', 'active'),
    (@catalogue_faculty_id, 'Master of Medicine in Pediatrics and Child Health', 'MPC', 'mpc', 3.0, 'Master''s Degree', 'active'),
    (@catalogue_faculty_id, 'Master of Public Health', 'MPH', 'mph', 2.0, 'Master''s Degree', 'active'),
    (@catalogue_faculty_id, 'Master of Science in Pharmacology (Drug Discovery)', 'MPM', 'mpm', 2.0, 'Master''s Degree', 'active'),
    (@catalogue_faculty_id, 'Master of Science in Sustainable Energy Engineering', 'MSE', 'mse', 2.0, 'Master''s Degree', 'active'),
    (@catalogue_faculty_id, 'Master of Science in Physics', 'MSP', 'msp', 2.0, 'Master''s Degree', 'active'),

    -- Postgraduate diplomas
    (@catalogue_faculty_id, 'Post Graduate Diploma in Public Administration', 'GDP', 'gdp', 1.0, 'Postgraduate Diploma', 'active'),
    (@catalogue_faculty_id, 'Post Graduate Diploma in Higher Education Pedagogy', 'GDE', 'gde', 1.0, 'Postgraduate Diploma', 'active'),

    -- Bachelor's programmes
    (@catalogue_faculty_id, 'Bachelor of Agricultural Mechanization and Irrigation Engineering', 'AMI', 'ami', 4.0, 'Bachelor''s Degree', 'active'),
    (@catalogue_faculty_id, 'Bachelor of Science in Agro-Processing Engineering', 'APE', 'ape', 4.0, 'Bachelor''s Degree', 'active'),
    (@catalogue_faculty_id, 'Bachelor of Animal Production and Management', 'APM', 'apm', 3.0, 'Bachelor''s Degree', 'active'),
    (@catalogue_faculty_id, 'Bachelor of Agribusiness', 'BAB', 'bab', 3.0, 'Bachelor''s Degree', 'active'),
    (@catalogue_faculty_id, 'Bachelor of Business Administration, Day / Weekend', 'BBA / BBW', 'bba-bbw', 3.0, 'Bachelor''s Degree', 'active'),
    (@catalogue_faculty_id, 'Bachelor of Science in Computer Engineering', 'BCT', 'bct', 4.0, 'Bachelor''s Degree', 'active'),
    (@catalogue_faculty_id, 'Bachelor of Science in Electrical Engineering', 'BEE', 'bee', 4.0, 'Bachelor''s Degree', 'active'),
    (@catalogue_faculty_id, 'Bachelor of Engineering in Mechanical Engineering', 'BEM', 'bem', 4.0, 'Bachelor''s Degree', 'active'),
    (@catalogue_faculty_id, 'Bachelor of Education Primary', 'BEP', 'bep', 2.0, 'Bachelor''s Degree', 'active'),
    (@catalogue_faculty_id, 'Bachelor of Science in Medical Education (IIHS)', 'BME', 'bme', 2.0, 'Bachelor''s Degree', 'active'),
    (@catalogue_faculty_id, 'Bachelor of Science in Anesthesia', 'BNA', 'bna', 4.0, 'Bachelor''s Degree', 'active'),
    (@catalogue_faculty_id, 'Bachelor of Nursing Science Completion (IIHS)', 'BNC', 'bnc', 3.0, 'Bachelor''s Degree', 'active'),
    (@catalogue_faculty_id, 'Bachelor of Science in Nursing', 'BNS', 'bns', 4.0, 'Bachelor''s Degree', 'active'),
    (@catalogue_faculty_id, 'Bachelor of Public Administration and Management', 'BPA', 'bpa', 3.0, 'Bachelor''s Degree', 'active'),
    (@catalogue_faculty_id, 'Bachelor of Procurement and Supply Chain Management', 'BPM', 'bpm', 3.0, 'Bachelor''s Degree', 'active'),
    (@catalogue_faculty_id, 'Bachelor of Science in Agriculture', 'BSA', 'bsa', 4.0, 'Bachelor''s Degree', 'active'),
    (@catalogue_faculty_id, 'Bachelor of Science in Statistics', 'BSS', 'bss', 3.0, 'Bachelor''s Degree', 'active'),
    (@catalogue_faculty_id, 'Bachelor of Information Technology', 'BTI', 'bti', 3.0, 'Bachelor''s Degree', 'active'),
    (@catalogue_faculty_id, 'Bachelor of Tourism and Travel Management', 'BTT', 'btt', 3.0, 'Bachelor''s Degree', 'active'),
    (@catalogue_faculty_id, 'Bachelor of Education Languages (English and Literature in English)', 'ELS', 'els', 3.0, 'Bachelor''s Degree', 'active'),
    (@catalogue_faculty_id, 'Bachelor of Entrepreneurship Development and Management', 'ENM', 'enm', 3.0, 'Bachelor''s Degree', 'active'),
    (@catalogue_faculty_id, 'Bachelor of Science in Fisheries and Water Resource Management', 'FWR', 'fwr', 3.0, 'Bachelor''s Degree', 'active'),
    (@catalogue_faculty_id, 'Bachelor of Science in Marine Engineering', 'MAE', 'mae', 4.0, 'Bachelor''s Degree', 'active'),
    (@catalogue_faculty_id, 'Bachelor of Science in Mining Engineering', 'MEB', 'meb', 4.0, 'Bachelor''s Degree', 'active'),
    (@catalogue_faculty_id, 'Bachelor of Medicine and Bachelor of Surgery', 'MED', 'med', 5.0, 'Bachelor''s Degree', 'active'),
    (@catalogue_faculty_id, 'Bachelor of Nursing Science Completion (Mulago School of Nursing)', 'NCS', 'ncs', 3.0, 'Bachelor''s Degree', 'active'),
    (@catalogue_faculty_id, 'Bachelor of Science in Natural Resource Economics', 'NRE', 'nre', 3.0, 'Bachelor''s Degree', 'active'),
    (@catalogue_faculty_id, 'Bachelor of Pharmacy', 'PHA', 'pha', 5.0, 'Bachelor''s Degree', 'active'),
    (@catalogue_faculty_id, 'Bachelor of Science Education', 'SCE', 'sce', 3.0, 'Bachelor''s Degree', 'active'),
    (@catalogue_faculty_id, 'Bachelor of Science in Computer Science', 'SCS', 'scs', 3.0, 'Bachelor''s Degree', 'active'),
    (@catalogue_faculty_id, 'Bachelor of Science in Polymer, Textile and Industrial Engineering', 'TEB', 'teb', 4.0, 'Bachelor''s Degree', 'active'),
    (@catalogue_faculty_id, 'Bachelor of Science in Water Resources Engineering', 'WAR', 'war', 4.0, 'Bachelor''s Degree', 'active'),

    -- Diploma programmes
    (@catalogue_faculty_id, 'Diploma in Agricultural Engineering', 'DAG', 'dag', 2.0, 'Diploma', 'active'),
    (@catalogue_faculty_id, 'Diploma in Animal Production and Management', 'DAP', 'dap', 2.0, 'Diploma', 'active'),
    (@catalogue_faculty_id, 'Diploma in Business Administration', 'DBA', 'dba', 2.0, 'Diploma', 'active'),
    (@catalogue_faculty_id, 'Diploma in Computer Engineering', 'DCE', 'dce', 2.0, 'Diploma', 'active'),
    (@catalogue_faculty_id, 'Diploma in Crop Production and Management', 'DCP', 'dcp', 2.0, 'Diploma', 'active'),
    (@catalogue_faculty_id, 'Diploma in Electronics and Electrical Engineering', 'DEE', 'dee', 2.0, 'Diploma', 'active'),
    (@catalogue_faculty_id, 'Diploma in Education Primary', 'DEP', 'dep', 2.0, 'Diploma', 'active'),
    (@catalogue_faculty_id, 'Diploma in Industrial and Ginning Engineering', 'DIG', 'dig', 2.0, 'Diploma', 'active'),
    (@catalogue_faculty_id, 'Diploma in Marine Engineering', 'DME', 'dme', 2.0, 'Diploma', 'active'),
    (@catalogue_faculty_id, 'Diploma in Nautical Sciences', 'DNS', 'dns', 2.0, 'Diploma', 'active'),
    (@catalogue_faculty_id, 'Diploma in Records and Information Management', 'DRI', 'dri', 2.0, 'Diploma', 'active'),
    (@catalogue_faculty_id, 'Diploma of Tourism and Travel Management', 'DTT', 'dtt', 2.0, 'Diploma', 'active'),
    (@catalogue_faculty_id, 'Diploma in Science Laboratory Technology (Biology)', 'SLB', 'slb', 2.0, 'Diploma', 'active'),
    (@catalogue_faculty_id, 'Diploma in Science Laboratory Technology (Chemistry)', 'SLC', 'slc', 2.0, 'Diploma', 'active'),
    (@catalogue_faculty_id, 'Diploma in Science Laboratory Technology (Physics)', 'SLP', 'slp', 2.0, 'Diploma', 'active'),

    -- General certificate
    (@catalogue_faculty_id, 'Certificate in General Agriculture', 'CGA', 'cga', NULL, 'Certificate', 'active'),

    -- Higher Education Access Certificate combinations
    (@catalogue_faculty_id, 'Higher Education Access Certificate (Biology and Agriculture)', 'HBA', 'hba', NULL, 'Higher Education Access Certificate', 'active'),
    (@catalogue_faculty_id, 'Higher Education Access Certificate (Biology and Chemistry)', 'HBC', 'hbc', NULL, 'Higher Education Access Certificate', 'active'),
    (@catalogue_faculty_id, 'Higher Education Access Certificate (Mathematics and Chemistry)', 'HMC', 'hmc', NULL, 'Higher Education Access Certificate', 'active'),
    (@catalogue_faculty_id, 'Higher Education Access Certificate (Physics and Mathematics)', 'HPM', 'hpm', NULL, 'Higher Education Access Certificate', 'active'),
    (@catalogue_faculty_id, 'Higher Education Access Certificate (English Language and Literature in English)', 'HEL', 'hel', NULL, 'Higher Education Access Certificate', 'active'),
    (@catalogue_faculty_id, 'Higher Education Access Certificate (History and Economics)', 'HHE', 'hhe', NULL, 'Higher Education Access Certificate', 'active'),
    (@catalogue_faculty_id, 'Higher Education Access Certificate (Economics and Geography)', 'HEG', 'heg', NULL, 'Higher Education Access Certificate', 'active'),

    -- Skills-based practical certificate courses
    (@catalogue_faculty_id, 'Certificate in Automotive Repair, Operation and Maintenance', 'CAM', 'cam', NULL, 'Certificate', 'active'),
    (@catalogue_faculty_id, 'Certificate in Welding and Metal Part Fabrication', 'CWM', 'cwm', NULL, 'Certificate', 'active'),
    (@catalogue_faculty_id, 'Certificate in Irrigation Technologies and Innovations', 'CIT', 'cit', NULL, 'Certificate', 'active'),
    (@catalogue_faculty_id, 'Certificate in Post-Harvest Handling and Processing Technologies', 'CPP', 'cpp', NULL, 'Certificate', 'active'),
    (@catalogue_faculty_id, 'Certificate in Brick Laying and Concrete Practice', 'CBC', 'cbc', NULL, 'Certificate', 'active')
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    duration = VALUES(duration),
    award_type = VALUES(award_type),
    status = VALUES(status);

COMMIT;
