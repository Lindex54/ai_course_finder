-- Step 3 (Skills and strengths): one 1 to 5 rating question per skill.
-- Ratings are stored in student_responses.numeric_response.
-- Safe to re-run.

INSERT INTO questions (question_text, question_type, category, is_required, display_order, status, ai_analysis_enabled)
SELECT 'How strong are you at problem solving?', 'scale', 'skills', 1, 1, 'active', 0
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM questions WHERE category = 'skills' AND display_order = 1);

INSERT INTO questions (question_text, question_type, category, is_required, display_order, status, ai_analysis_enabled)
SELECT 'How strong are you at creativity?', 'scale', 'skills', 1, 2, 'active', 0
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM questions WHERE category = 'skills' AND display_order = 2);

INSERT INTO questions (question_text, question_type, category, is_required, display_order, status, ai_analysis_enabled)
SELECT 'How strong are you at communication?', 'scale', 'skills', 1, 3, 'active', 0
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM questions WHERE category = 'skills' AND display_order = 3);

INSERT INTO questions (question_text, question_type, category, is_required, display_order, status, ai_analysis_enabled)
SELECT 'How strong are you at analytical thinking?', 'scale', 'skills', 1, 4, 'active', 0
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM questions WHERE category = 'skills' AND display_order = 4);
