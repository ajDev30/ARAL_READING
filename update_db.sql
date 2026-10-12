CREATE TABLE IF NOT EXISTS settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value TEXT
);
INSERT IGNORE INTO settings (setting_key, setting_value) VALUES 
('asr_enabled', '1'),
('azure_openai_key', ''),
('openai_model', 'gpt-4o-realtime-preview-2024-10-01'),
('azure_region', 'eastus'),
('azure_language', 'en-US'),
('tts_voice', 'alloy'),
('tts_personality', 'You are a friendly teacher.'),
('tts_model', 'tts-1');
