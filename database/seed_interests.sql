-- Step 2 (Interests): the three questions and their answer options.
-- Subject options are taken from the subjects table, so they match step 1.
-- Activity and interest-area options are a starter list; staff can edit them later.
-- Safe to re-run.

INSERT INTO questions (question_text, question_type, category, is_required, display_order, status, ai_analysis_enabled)
SELECT 'Which subjects do you enjoy most?', 'multiple_choice', 'interests', 1, 1, 'active', 0
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM questions WHERE category = 'interests' AND display_order = 1);

INSERT INTO questions (question_text, question_type, category, is_required, display_order, status, ai_analysis_enabled)
SELECT 'Which activities do you enjoy?', 'multiple_choice', 'interests', 1, 2, 'active', 0
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM questions WHERE category = 'interests' AND display_order = 2);

INSERT INTO questions (question_text, question_type, category, is_required, display_order, status, ai_analysis_enabled)
SELECT 'Which areas of work or study interest you?', 'multiple_choice', 'interests', 1, 3, 'active', 0
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM questions WHERE category = 'interests' AND display_order = 3);

-- Question 1: one option per distinct subject, from the subjects table.
INSERT INTO question_options (question_id, option_text, option_value, display_order, status)
SELECT q.id, MIN(s.name), s.slug, MIN(s.display_order), 'active'
FROM questions q
JOIN subjects s ON s.status = 'active' AND s.category <> 'General and subsidiary'
WHERE q.category = 'interests' AND q.display_order = 1
GROUP BY q.id, s.slug
ON DUPLICATE KEY UPDATE option_text = VALUES(option_text), display_order = VALUES(display_order);

-- Question 2: activities.
INSERT INTO question_options (question_id, option_text, option_value, display_order, status)
SELECT q.id, v.option_text, v.option_value, v.display_order, 'active'
FROM questions q
JOIN (
    SELECT 'Building or making things' AS option_text, 'building-making' AS option_value, 10 AS display_order
    UNION ALL SELECT 'Solving puzzles or problems', 'solving-problems', 20
    UNION ALL SELECT 'Helping or caring for people', 'helping-people', 30
    UNION ALL SELECT 'Writing or reading', 'writing-reading', 40
    UNION ALL SELECT 'Working with numbers or data', 'numbers-data', 50
    UNION ALL SELECT 'Working outdoors or with plants and animals', 'outdoors-nature', 60
    UNION ALL SELECT 'Working with computers or technology', 'computers-technology', 70
    UNION ALL SELECT 'Performing or presenting', 'performing-presenting', 80
    UNION ALL SELECT 'Drawing, design or art', 'drawing-design', 90
    UNION ALL SELECT 'Organising events or teams', 'organising-teams', 100
    UNION ALL SELECT 'Travelling or meeting new people', 'travel-people', 110
    UNION ALL SELECT 'Experimenting in a laboratory', 'laboratory-experiments', 120
) v
WHERE q.category = 'interests' AND q.display_order = 2
ON DUPLICATE KEY UPDATE option_text = VALUES(option_text), display_order = VALUES(display_order);

-- Question 3: areas of work or study.
INSERT INTO question_options (question_id, option_text, option_value, display_order, status)
SELECT q.id, v.option_text, v.option_value, v.display_order, 'active'
FROM questions q
JOIN (
    SELECT 'Health and medicine' AS option_text, 'health-medicine' AS option_value, 10 AS display_order
    UNION ALL SELECT 'Engineering and construction', 'engineering-construction', 20
    UNION ALL SELECT 'Agriculture and food', 'agriculture-food', 30
    UNION ALL SELECT 'Business and finance', 'business-finance', 40
    UNION ALL SELECT 'Information technology', 'information-technology', 50
    UNION ALL SELECT 'Law and public service', 'law-public-service', 60
    UNION ALL SELECT 'Education and teaching', 'education-teaching', 70
    UNION ALL SELECT 'Environment and natural resources', 'environment-resources', 80
    UNION ALL SELECT 'Arts, media and design', 'arts-media-design', 90
    UNION ALL SELECT 'Scientific research', 'scientific-research', 100
    UNION ALL SELECT 'Tourism and hospitality', 'tourism-hospitality', 110
    UNION ALL SELECT 'Sports and fitness', 'sports-fitness', 120
    UNION ALL SELECT 'Social work and community development', 'social-community', 130
) v
WHERE q.category = 'interests' AND q.display_order = 3
ON DUPLICATE KEY UPDATE option_text = VALUES(option_text), display_order = VALUES(display_order);
