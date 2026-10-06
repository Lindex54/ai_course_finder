-- Step 4 (Career goals) and step 5 (free-text answer).
-- Career and work options are a starter list that staff can edit later.
-- Single-choice questions store one option per student; the career
-- interests question allows up to five.
-- Safe to re-run.

INSERT INTO questions (question_text, question_type, category, is_required, display_order, status, ai_analysis_enabled)
SELECT 'Which careers are you considering?', 'multiple_choice', 'career', 1, 1, 'active', 0
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM questions WHERE category = 'career' AND display_order = 1);

INSERT INTO questions (question_text, question_type, category, is_required, display_order, status, ai_analysis_enabled)
SELECT 'What kind of work would you prefer?', 'single_choice', 'career', 1, 2, 'active', 0
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM questions WHERE category = 'career' AND display_order = 2);

INSERT INTO questions (question_text, question_type, category, is_required, display_order, status, ai_analysis_enabled)
SELECT 'What are your future ambitions?', 'single_choice', 'career', 1, 3, 'active', 0
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM questions WHERE category = 'career' AND display_order = 3);

INSERT INTO questions (question_text, question_type, category, is_required, display_order, status, ai_analysis_enabled)
SELECT 'What do you want your future to look like?', 'text', 'free_text', 1, 1, 'active', 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM questions WHERE category = 'free_text' AND display_order = 1);

-- Question 1: careers (multiple choice).
INSERT INTO question_options (question_id, option_text, option_value, display_order, status)
SELECT q.id, v.option_text, v.option_value, v.display_order, 'active'
FROM questions q
JOIN (
    SELECT 'Doctor, nurse or health worker' AS option_text, 'health-worker' AS option_value, 10 AS display_order
    UNION ALL SELECT 'Engineer', 'engineer', 20
    UNION ALL SELECT 'Teacher or lecturer', 'teacher-lecturer', 30
    UNION ALL SELECT 'Accountant, banker or financial analyst', 'finance', 40
    UNION ALL SELECT 'Software developer or IT specialist', 'software-it', 50
    UNION ALL SELECT 'Agronomist, farmer or agribusiness manager', 'agriculture', 60
    UNION ALL SELECT 'Lawyer or legal officer', 'law', 70
    UNION ALL SELECT 'Scientist or researcher', 'scientist-researcher', 80
    UNION ALL SELECT 'Entrepreneur or business owner', 'entrepreneur', 90
    UNION ALL SELECT 'Civil servant or public administrator', 'public-administration', 100
    UNION ALL SELECT 'Journalist, writer or media professional', 'media', 110
    UNION ALL SELECT 'Pharmacist or laboratory technologist', 'pharmacy-lab', 120
    UNION ALL SELECT 'Veterinary or animal health officer', 'veterinary', 130
    UNION ALL SELECT 'Tourism or hospitality manager', 'tourism', 140
    UNION ALL SELECT 'Social worker or community development officer', 'social-work', 150
    UNION ALL SELECT 'Architect, planner or surveyor', 'architecture-planning', 160
    UNION ALL SELECT 'Artist or designer', 'arts-design', 170
) v
WHERE q.category = 'career' AND q.display_order = 1
ON DUPLICATE KEY UPDATE option_text = VALUES(option_text), display_order = VALUES(display_order);

-- Question 2: preferred work (single choice).
INSERT INTO question_options (question_id, option_text, option_value, display_order, status)
SELECT q.id, v.option_text, v.option_value, v.display_order, 'active'
FROM questions q
JOIN (
    SELECT 'Office or desk-based' AS option_text, 'office' AS option_value, 10 AS display_order
    UNION ALL SELECT 'Outdoors or in the field', 'field', 20
    UNION ALL SELECT 'Laboratory or workshop', 'lab-workshop', 30
    UNION ALL SELECT 'Creative studio', 'studio', 40
    UNION ALL SELECT 'Working closely with people', 'people', 50
    UNION ALL SELECT 'Running my own business', 'own-business', 60
    UNION ALL SELECT 'A mix, depending on the job', 'mixed', 70
) v
WHERE q.category = 'career' AND q.display_order = 2
ON DUPLICATE KEY UPDATE option_text = VALUES(option_text), display_order = VALUES(display_order);

-- Question 3: future ambitions (single choice).
INSERT INTO question_options (question_id, option_text, option_value, display_order, status)
SELECT q.id, v.option_text, v.option_value, v.display_order, 'active'
FROM questions q
JOIN (
    SELECT 'Get a job straight after graduating' AS option_text, 'first-job' AS option_value, 10 AS display_order
    UNION ALL SELECT 'Work in industry or a company', 'industry', 20
    UNION ALL SELECT 'Start my own business', 'start-business', 30
    UNION ALL SELECT 'Continue to postgraduate study', 'postgraduate', 40
    UNION ALL SELECT 'Serve in public service', 'public-service', 50
    UNION ALL SELECT 'Not sure yet', 'not-sure', 60
) v
WHERE q.category = 'career' AND q.display_order = 3
ON DUPLICATE KEY UPDATE option_text = VALUES(option_text), display_order = VALUES(display_order);
