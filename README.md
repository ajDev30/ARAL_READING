# ServerSide_ARAL_READING

Server-side engine and web application for the ARAL Reading Assessment Program.

## Features
- Core Phil-IRI Engine & Assessment Scoring
- Azure Speech Pronunciation Assessment integration
- OpenAI Realtime transcription integration
- Teacher & Student Dashboards
- Course assessments (Pre-Test and Post-Test for Grade levels 7, 8, 9, 10)
- Group Screening Test (GST) management
- Real-time diagnostic transcript review with teacher override

## Database Setup
1. **Schema only**: Import `schema.sql` to initialize database tables:
   ```bash
   mysql -u <user> -p <database> < schema.sql
   ```
2. **Full dump**: Alternatively, import `aral_reading_db_dump.sql` to include passages, assessments, and sample data.

## Configuration
Copy `v536/.env.example` to `v536/.env` and provide your credentials:
```bash
cp v536/.env.example v536/.env
```
Configure your Azure Speech and Azure OpenAI API keys in `v536/.env`:
- `AZURE_OPENAI_API_KEY`
- `AZURE_SPEECH_KEY`
- `AZURE_REGION`
- `AZURE_LANGUAGE`
- `OPENAI_MODEL`
