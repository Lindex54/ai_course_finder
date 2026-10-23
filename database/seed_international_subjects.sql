-- International secondary-school route and its subject list.
--
-- The list uses broadly recognised Cambridge/IB-style subject names and is
-- intentionally limited to subjects connected to Busitema University's current
-- bachelor's programme areas. Language-only subjects without a matching programme
-- pathway (for example Arabic) are not included.
-- Safe to re-run.

ALTER TABLE student_sessions
    MODIFY highest_level ENUM('o_level', 'a_level', 'international') NULL;

ALTER TABLE subjects
    MODIFY level ENUM('o_level', 'a_level', 'international') NOT NULL;

INSERT INTO subjects (name, slug, level, category, display_order, status) VALUES
    ('Mathematics', 'mathematics', 'international', 'Mathematics', 10, 'active'),
    ('Further or Additional Mathematics', 'further-mathematics', 'international', 'Mathematics', 20, 'active'),
    ('Physics', 'physics', 'international', 'Sciences', 30, 'active'),
    ('Chemistry', 'chemistry', 'international', 'Sciences', 40, 'active'),
    ('Biology', 'biology', 'international', 'Sciences', 50, 'active'),
    ('Human Biology or Health Science', 'human-biology-health-science', 'international', 'Sciences', 60, 'active'),
    ('Combined or Coordinated Science', 'combined-science', 'international', 'Sciences', 70, 'active'),
    ('Agriculture', 'agriculture', 'international', 'Agriculture and environment', 80, 'active'),
    ('Environmental Science or Environmental Management', 'environmental-science', 'international', 'Agriculture and environment', 90, 'active'),
    ('Marine Science', 'marine-science', 'international', 'Agriculture and environment', 100, 'active'),
    ('Computer Science', 'computer-science', 'international', 'Computing and technology', 110, 'active'),
    ('Information and Communication Technology', 'ict', 'international', 'Computing and technology', 120, 'active'),
    ('Design and Technology or Technical Drawing', 'design-technology', 'international', 'Computing and technology', 130, 'active'),
    ('English Language', 'english-language', 'international', 'Languages and education', 140, 'active'),
    ('English Literature or Literature in English', 'literature-in-english', 'international', 'Languages and education', 150, 'active'),
    ('Geography', 'geography', 'international', 'Humanities and social sciences', 160, 'active'),
    ('History', 'history', 'international', 'Humanities and social sciences', 170, 'active'),
    ('Economics', 'economics', 'international', 'Business and economics', 180, 'active'),
    ('Business Studies or Business Management', 'business-studies', 'international', 'Business and economics', 190, 'active'),
    ('Accounting', 'accounting', 'international', 'Business and economics', 200, 'active'),
    ('Enterprise or Entrepreneurship', 'entrepreneurship', 'international', 'Business and economics', 210, 'active'),
    ('Food and Nutrition or Food Science', 'food-nutrition', 'international', 'Applied subjects', 220, 'active'),
    ('Travel and Tourism', 'travel-tourism', 'international', 'Applied subjects', 230, 'active'),
    ('Physical Education or Sports Science', 'physical-education', 'international', 'Applied subjects', 240, 'active')
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    category = VALUES(category),
    display_order = VALUES(display_order),
    status = VALUES(status);
