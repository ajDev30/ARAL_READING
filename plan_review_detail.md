# Plan for `review_detail.php`

To achieve this, we need to make significant architectural changes. Currently, the system is designed to process audio *in-memory* and throw it away, and it only saves the final scores (WPM, Accuracy) to the database, not the raw word-by-word data.

## Phase 1: Capturing the Missing Data
1. **Save the Audio File:** 
   Currently, the audio is streamed to Azure in real-time and never saved to the hard drive. We need to update `app.js` so it *simultaneously* records the microphone to a `.webm` file using `MediaRecorder`, and uploads that file to your server when the student clicks "Submit".
2. **Save the Markup Data:** 
   Currently, `cascading-flow.js` only tells the database "Accuracy: 95%, 1 omission". We need to update it to send the complete `currentAssessment` JSON object (which contains exactly which words were crossed out, highlighted, etc.) and save it into the `evaluation_data` column in the database.
3. **Save GST Answers:** 
   Update `api_assessment.php` to save the specific JSON of which answers the student picked during the GST, not just their final score.

## Phase 2: Building the Teacher UI (`review_detail.php`)
1. **The Header:** Display the student's name, grade, date, and their GST performance.
2. **The Audio Player:** Add an HTML5 `<audio>` player linked to the `.webm` file we saved in Phase 1 so the teacher can listen to the student's voice.
3. **The Transcript Pane:** Inject the exact same CSS and Javascript from the student's view into the teacher's view, so the teacher sees the identical highlighted markup (cross-outs, omissions).
4. **The Scoreboard:** Display the WPM, WCPM, Comprehension, and Accuracy.

## Phase 3: The Override Engine
1. **Interactive Transcript:** Make the words in the teacher's transcript pane clickable.
2. **Override Menu:** When the teacher clicks a word, pop up a menu allowing them to manually mark it as an Omission, Substitution, Mispronunciation, or Self-Corrected.
3. **Auto-Compute:** When the teacher changes a mark, re-run the `renderPerformance` javascript engine to instantly recalculate the Accuracy % and WCPM on the screen.
4. **Save Override:** Add a "Save Final Override" button that updates the database with the teacher's new, official scores.

