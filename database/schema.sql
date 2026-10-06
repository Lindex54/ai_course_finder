-- AI Course Finder core schema
-- Compatible with modern MySQL and MariaDB.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS faculties (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50) NULL,
    description TEXT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_faculties_name (name),
    UNIQUE KEY uq_faculties_code (code),
    KEY idx_faculties_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS programmes (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    faculty_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50) NULL,
    slug VARCHAR(255) NOT NULL,
    description TEXT NULL,
    duration DECIMAL(4,1) NULL,
    minimum_points TINYINT UNSIGNED NULL,
    related_subjects VARCHAR(255) NULL,
    field_of_study VARCHAR(100) NULL,
    a_level_rules LONGTEXT NULL,
    a_level_requirement TEXT NULL,
    award_type VARCHAR(100) NULL,
    programme_url VARCHAR(500) NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_programmes_code (code),
    UNIQUE KEY uq_programmes_slug (slug),
    KEY idx_programmes_faculty_id (faculty_id),
    KEY idx_programmes_status (status),
    CONSTRAINT fk_programmes_faculty
        FOREIGN KEY (faculty_id) REFERENCES faculties (id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS attributes (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(150) NOT NULL,
    category VARCHAR(100) NOT NULL,
    description TEXT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_attributes_slug (slug),
    KEY idx_attributes_category (category),
    KEY idx_attributes_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS programme_attributes (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    programme_id BIGINT UNSIGNED NOT NULL,
    attribute_id BIGINT UNSIGNED NOT NULL,
    weight DECIMAL(5,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_programme_attributes_pair (programme_id, attribute_id),
    KEY idx_programme_attributes_attribute_id (attribute_id),
    CONSTRAINT fk_programme_attributes_programme
        FOREIGN KEY (programme_id) REFERENCES programmes (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_programme_attributes_attribute
        FOREIGN KEY (attribute_id) REFERENCES attributes (id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS questions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    question_text TEXT NOT NULL,
    question_type ENUM('single_choice', 'multiple_choice', 'scale', 'text') NOT NULL,
    category VARCHAR(100) NULL,
    is_required BOOLEAN NOT NULL DEFAULT TRUE,
    display_order INT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    ai_analysis_enabled BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_questions_status (status),
    KEY idx_questions_category (category),
    KEY idx_questions_display_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS question_options (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    question_id BIGINT UNSIGNED NOT NULL,
    option_text VARCHAR(500) NOT NULL,
    option_value VARCHAR(255) NULL,
    display_order INT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_question_options_value (question_id, option_value),
    KEY idx_question_options_question_id (question_id),
    KEY idx_question_options_display_order (display_order),
    CONSTRAINT fk_question_options_question
        FOREIGN KEY (question_id) REFERENCES questions (id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS option_attributes (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    option_id BIGINT UNSIGNED NOT NULL,
    attribute_id BIGINT UNSIGNED NOT NULL,
    score DECIMAL(5,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_option_attributes_pair (option_id, attribute_id),
    KEY idx_option_attributes_attribute_id (attribute_id),
    CONSTRAINT fk_option_attributes_option
        FOREIGN KEY (option_id) REFERENCES question_options (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_option_attributes_attribute
        FOREIGN KEY (attribute_id) REFERENCES attributes (id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS student_sessions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    session_token VARCHAR(128) NOT NULL,
    highest_level ENUM('o_level', 'a_level') NULL,
    started_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL DEFAULT NULL,
    status ENUM('in_progress', 'completed', 'abandoned') NOT NULL DEFAULT 'in_progress',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_student_sessions_token (session_token),
    KEY idx_student_sessions_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS student_responses (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    session_id BIGINT UNSIGNED NOT NULL,
    question_id BIGINT UNSIGNED NOT NULL,
    option_id BIGINT UNSIGNED NULL,
    text_response TEXT NULL,
    numeric_response DECIMAL(10,2) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_student_responses_session_id (session_id),
    KEY idx_student_responses_question_id (question_id),
    KEY idx_student_responses_option_id (option_id),
    KEY idx_student_responses_session_question (session_id, question_id),
    CONSTRAINT fk_student_responses_session
        FOREIGN KEY (session_id) REFERENCES student_sessions (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_student_responses_question
        FOREIGN KEY (question_id) REFERENCES questions (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_student_responses_option
        FOREIGN KEY (option_id) REFERENCES question_options (id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS recommendations (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    session_id BIGINT UNSIGNED NOT NULL,
    programme_id BIGINT UNSIGNED NOT NULL,
    match_score DECIMAL(5,2) NOT NULL,
    rank_position INT UNSIGNED NULL,
    explanation TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_recommendations_session_programme (session_id, programme_id),
    KEY idx_recommendations_session_id (session_id),
    KEY idx_recommendations_programme_id (programme_id),
    KEY idx_recommendations_rank_position (rank_position),
    CONSTRAINT fk_recommendations_session
        FOREIGN KEY (session_id) REFERENCES student_sessions (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_recommendations_programme
        FOREIGN KEY (programme_id) REFERENCES programmes (id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS subjects (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(150) NOT NULL,
    level ENUM('o_level', 'a_level') NOT NULL,
    category VARCHAR(100) NOT NULL,
    display_order INT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_subjects_slug_level (slug, level),
    KEY idx_subjects_level (level),
    KEY idx_subjects_category (category),
    KEY idx_subjects_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS student_subjects (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    session_id BIGINT UNSIGNED NOT NULL,
    subject_id BIGINT UNSIGNED NOT NULL,
    role ENUM('ordinary', 'principal', 'subsidiary', 'general') NOT NULL DEFAULT 'ordinary',
    performance ENUM('strong', 'average', 'weak') NULL,
    grade VARCHAR(10) NULL,
    points TINYINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_student_subjects_pair (session_id, subject_id),
    KEY idx_student_subjects_subject_id (subject_id),
    CONSTRAINT fk_student_subjects_session
        FOREIGN KEY (session_id) REFERENCES student_sessions (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_student_subjects_subject
        FOREIGN KEY (subject_id) REFERENCES subjects (id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ai_analyses (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    session_id BIGINT UNSIGNED NOT NULL,
    model VARCHAR(100) NOT NULL,
    status ENUM('completed', 'failed') NOT NULL,
    input_profile LONGTEXT NULL,
    result_json LONGTEXT NULL,
    error_message TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_ai_analyses_session_id (session_id),
    CONSTRAINT fk_ai_analyses_session
        FOREIGN KEY (session_id) REFERENCES student_sessions (id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
