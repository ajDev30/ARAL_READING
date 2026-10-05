<?php
$file = '/var/www/andrew/ARAL_READING/v536/public/cascading-flow.js';
$content = file_get_contents($file);

$old_gst = <<<'EOD'
async function loadGSTContent() {
    readerSection.classList.add('d-none');
    gstSection.classList.remove('d-none');
    
    // Fetch GST questions for the student's current enrolled grade
    const res = await fetch(`api_assessment.php?action=get_gst&grade=${studentCurrentGrade}`);
    const data = await res.json();
    
    if(!data.questions || data.questions.length === 0) {
        gstQuestionsContainer.innerHTML = `<p class="text-danger">No GST content found for Grade ${studentCurrentGrade}. Please add GST questions in the teacher portal.</p>`;
        return;
    }

    window.currentGstQuestions = data.questions;
    renderGSTUI(data.questions);
}
EOD;

$new_gst = <<<'EOD'
let gstTimerInterval = null;

async function loadGSTContent() {
    readerSection.classList.add('d-none');
    gstSection.classList.remove('d-none');
    
    // Fetch GST questions for the student's current enrolled grade
    const res = await fetch(`api_assessment.php?action=get_gst&grade=${studentCurrentGrade}`);
    const data = await res.json();
    
    if(!data.questions || data.questions.length === 0) {
        gstQuestionsContainer.innerHTML = `<p class="text-danger">No GST content found for Grade ${studentCurrentGrade}. Please add GST questions in the teacher portal.</p>`;
        return;
    }

    window.currentGstQuestions = data.questions;
    renderGSTUI(data.questions);

    // Setup Timer
    if (data.time_limit && data.time_limit > 0) {
        const timerContainer = document.getElementById('gst-timer-container');
        const timerDisplay = document.getElementById('gst-timer-display');
        if (timerContainer && timerDisplay) {
            timerContainer.classList.remove('d-none');
            let timeLeft = data.time_limit * 60; // in seconds
            
            function updateDisplay() {
                let m = Math.floor(timeLeft / 60);
                let s = timeLeft % 60;
                timerDisplay.innerText = (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
                if (timeLeft <= 60) timerContainer.classList.replace('alert-warning', 'alert-danger');
            }
            updateDisplay();

            gstTimerInterval = setInterval(() => {
                timeLeft--;
                updateDisplay();
                if (timeLeft <= 0) {
                    clearInterval(gstTimerInterval);
                    alert("Time is up! Your GST will be submitted automatically.");
                    if(gstSubmitBtn) gstSubmitBtn.click();
                }
            }, 1000);
        }
    }
}
EOD;

$content = str_replace($old_gst, $new_gst, $content);
file_put_contents($file, $content);
echo "Updated cascading-flow.js\n";
