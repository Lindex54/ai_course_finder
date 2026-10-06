-- Subjects from the Uganda national examinations system (UNEB).
-- O-level = UCE (competency-based curriculum). A-level = UACE principal,
-- general and subsidiary papers. Subject codes are not stored because they
-- could not be verified against an official UNEB document.
-- Safe to re-run.

INSERT INTO subjects (name, slug, level, category, display_order, status) VALUES
    -- O-level (UCE)
    ('Mathematics', 'mathematics', 'o_level', 'Sciences', 10, 'active'),
    ('Physics', 'physics', 'o_level', 'Sciences', 20, 'active'),
    ('Chemistry', 'chemistry', 'o_level', 'Sciences', 30, 'active'),
    ('Biology', 'biology', 'o_level', 'Sciences', 40, 'active'),
    ('Agriculture', 'agriculture', 'o_level', 'Sciences', 50, 'active'),
    ('English Language', 'english-language', 'o_level', 'Languages', 60, 'active'),
    ('Kiswahili', 'kiswahili', 'o_level', 'Languages', 70, 'active'),
    ('Foreign Languages', 'foreign-languages', 'o_level', 'Languages', 80, 'active'),
    ('Local Languages', 'local-languages', 'o_level', 'Languages', 90, 'active'),
    ('Literature in English', 'literature-in-english', 'o_level', 'Languages', 100, 'active'),
    ('History and Political Education', 'history-and-political-education', 'o_level', 'Humanities', 110, 'active'),
    ('Geography', 'geography', 'o_level', 'Humanities', 120, 'active'),
    ('Religious Education (CRE or IRE)', 'religious-education', 'o_level', 'Humanities', 130, 'active'),
    ('Information and Communication Technology', 'ict', 'o_level', 'Technology and vocational', 140, 'active'),
    ('Technology and Design', 'technology-and-design', 'o_level', 'Technology and vocational', 150, 'active'),
    ('Art and Design', 'art-and-design', 'o_level', 'Technology and vocational', 160, 'active'),
    ('Performing Arts', 'performing-arts', 'o_level', 'Technology and vocational', 170, 'active'),
    ('Nutrition and Food Technology', 'nutrition-and-food-technology', 'o_level', 'Technology and vocational', 180, 'active'),
    ('Entrepreneurship', 'entrepreneurship', 'o_level', 'Business', 190, 'active'),
    ('Physical Education', 'physical-education', 'o_level', 'Sports and health', 200, 'active'),
    -- A-level (UACE) principal subjects
    ('Mathematics', 'mathematics', 'a_level', 'Sciences', 10, 'active'),
    ('Physics', 'physics', 'a_level', 'Sciences', 20, 'active'),
    ('Chemistry', 'chemistry', 'a_level', 'Sciences', 30, 'active'),
    ('Biology', 'biology', 'a_level', 'Sciences', 40, 'active'),
    ('Agriculture', 'agriculture', 'a_level', 'Sciences', 50, 'active'),
    ('English Language', 'english-language', 'a_level', 'Languages', 60, 'active'),
    ('Literature in English', 'literature-in-english', 'a_level', 'Languages', 70, 'active'),
    ('Foreign Languages (French, German, Latin, Kiswahili)', 'foreign-languages', 'a_level', 'Languages', 80, 'active'),
    ('History', 'history', 'a_level', 'Humanities', 90, 'active'),
    ('Geography', 'geography', 'a_level', 'Humanities', 100, 'active'),
    ('Economics', 'economics', 'a_level', 'Business', 110, 'active'),
    ('Entrepreneurship Education', 'entrepreneurship-education', 'a_level', 'Business', 120, 'active'),
    ('Art', 'art', 'a_level', 'Technology and vocational', 130, 'active'),
    ('Foods and Nutrition', 'foods-and-nutrition', 'a_level', 'Technology and vocational', 140, 'active'),
    -- A-level (UACE) general and subsidiary papers
    ('General Paper', 'general-paper', 'a_level', 'General and subsidiary', 150, 'active'),
    ('Subsidiary Mathematics', 'subsidiary-mathematics', 'a_level', 'General and subsidiary', 160, 'active'),
    ('Subsidiary ICT', 'subsidiary-ict', 'a_level', 'General and subsidiary', 170, 'active')
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    category = VALUES(category),
    display_order = VALUES(display_order);
