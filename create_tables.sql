CREATE TABLE reading_passages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    grade_level VARCHAR(50) NOT NULL,
    passage_text TEXT,
    questions_json TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE reading_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    passage_id INT NOT NULL,
    phase VARCHAR(50) NOT NULL, -- e.g., 'Pre-Assessment', 'Intervention Pre-Test', 'Post-Test'
    transcript TEXT,
    accuracy_score FLOAT DEFAULT 0.0,
    reading_time INT DEFAULT 0,
    reading_speed FLOAT DEFAULT 0.0,
    miscues_json TEXT,
    answers_json TEXT,
    evaluation_data TEXT, -- Azure JSON
    comprehension_score FLOAT DEFAULT 0.0,
    oral_reading_profile VARCHAR(50) DEFAULT 'Pending', -- Independent, Instructional, Frustration
    status VARCHAR(50) DEFAULT 'Pending Review',
    audio_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (passage_id) REFERENCES reading_passages(id)
);
