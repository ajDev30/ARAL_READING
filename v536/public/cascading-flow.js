// 1. STATE VARIABLES
const gstSection = document.getElementById('gst-section');
const gstQuestionsContainer = document.getElementById('gst-questions');
const gstSubmitBtn = document.getElementById('submitGstBtn');
const readerSection = document.getElementById('reader-section');
const transitionSection = document.getElementById('transition-section');
const transitionContent = document.getElementById('transition-content');
const continueBtn = document.getElementById('continueAssessmentBtn');

const assessmentModal = document.getElementById('assessmentModal');
const questionsContainer = document.getElementById('questionsContainer');
const submitAssessmentBtn = document.getElementById('submitAssessmentBtn');

const studentCurrentGrade = window.STUDENT_GRADE || 7; 

let gstResult = {
    score: null,
    totalItems: null,
    percentage: null,
    startingGrade: null,
    category: null,
    needsIndividualAssessment: false,
    completed: false
};

let readingProfile = {
    independentGrade: null,
    instructionalGrade: null,
    frustrationGrade: null
};

let testedGrades = {};
let currentTestingGrade = null;
let currentPassageData = null;
let gstTimerInterval = null;

// PHIL-IRI RULES
const GST_RULES = {
    noIndividualAssessmentMin: 28,
    middleRangeMin: 16,
    middleGradesToStartBelow: 2,
    lowerGradesToStartBelow: 3
};

// 2. INITIALIZATION
async function initializeAssessmentFlow() {
    // Hide everything
    gstSection.classList.add('d-none');
    readerSection.classList.add('d-none');
    if(transitionSection) transitionSection.classList.add('d-none');

    // Fetch student's current assessment status
    const res = await fetch('api_assessment.php?action=get_status');
    const status = await res.json();

    if (!status.gst_completed) {
        await loadGSTContent();
    } else {
        // Load existing state and normalize db columns to camelCase
        gstResult = status.gst_result;
        if (gstResult) {
            gstResult.startingGrade = gstResult.starting_grade !== null ? parseInt(gstResult.starting_grade) : null;
            gstResult.needsIndividualAssessment = gstResult.needs_individual_assessment !== null ? parseInt(gstResult.needs_individual_assessment) : null;
            gstResult.category = gstResult.gst_category;
        }
        testedGrades = status.tested_grades || {};
        
        if (gstResult.score >= GST_RULES.noIndividualAssessmentMin) {
            showFinalProfileUI("Learner scored " + gstResult.score + " on GST. No individualized assessment required.");
            return;
        }

        const nextGrade = determineNextAssessmentGrade();
        if (nextGrade === null) {
            calculateFinalProfile();
            showFinalProfileUI("Reading profile complete.");
        } else {
            startGradedPassage(nextGrade);
        }
    }
}

// 3. GST EXECUTION
async function loadGSTContent() {
    gstSection.classList.remove('d-none');
    const res = await fetch(`api_assessment.php?action=get_gst&grade=${studentCurrentGrade}`);
    const data = await res.json();
    
    if(!data.questions || data.questions.length === 0) {
        gstQuestionsContainer.innerHTML = `<p class="text-danger">No GST content found for Grade ${studentCurrentGrade}. Please add GST questions in the teacher portal.</p>`;
        return;
    }
    window.currentGstQuestions = data.questions;
    renderGSTUI(data.questions);

    // Timer
    if (data.time_limit && data.time_limit > 0) {
        const timerContainer = document.getElementById('gst-timer-container');
        const timerDisplay = document.getElementById('gst-timer-display');
        if (timerContainer && timerDisplay) {
            timerContainer.classList.remove('d-none');
            let timeLeft = data.time_limit * 60;
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

function renderGSTUI(questions) {
    gstQuestionsContainer.innerHTML = '';
    questions.forEach((q, idx) => {
        let html = `<div class="mb-4 q-block p-4 border rounded bg-white" data-idx="${idx}" data-type="${q.type || 'multichoice'}">
            <h5 class="font-weight-bold mb-3">${idx + 1}. ${q.question || q.text || ''}</h5>`;
            
        if (q.type === 'multichoice' || !q.type || q.options) {
            (q.options || []).forEach((opt, oIdx) => {
                html += `<div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="gst_q_${idx}" value="${oIdx}" id="gst_q_${idx}_${oIdx}">
                    <label class="form-check-label" for="gst_q_${idx}_${oIdx}">${opt}</label>
                </div>`;
            });
        } else if (q.type === 'truefalse') {
            html += `<div class="form-check mb-2"><input class="form-check-input" type="radio" name="gst_q_${idx}" value="true" id="gst_q_${idx}_t"><label class="form-check-label" for="gst_q_${idx}_t">True</label></div>
                     <div class="form-check mb-2"><input class="form-check-input" type="radio" name="gst_q_${idx}" value="false" id="gst_q_${idx}_f"><label class="form-check-label" for="gst_q_${idx}_f">False</label></div>`;
        } else if (q.type === 'enumeration') {
            const count = (q.answers && q.answers.length > 0) ? q.answers.length : (parseInt(q.count) || 3);
            for(let c=0; c<count; c++) html += `<input type="text" class="form-control mb-2 enum-input" placeholder="Item ${c+1}">`;
        } else if (q.type === 'essay') {
            html += `<textarea class="form-control essay-input" rows="3" placeholder="Type your answer..."></textarea>`;
        }
        
        html += `</div>`;
        gstQuestionsContainer.innerHTML += html;
    });
}

if(gstSubmitBtn) {
        gstSubmitBtn.addEventListener('click', async () => {
        if(gstTimerInterval) clearInterval(gstTimerInterval);
        
        let score = 0;
        let total = 0;
        
        window.currentGstQuestions.forEach((q, idx) => {
            if (q.type === 'multichoice' || !q.type || q.options) {
                total++;
                const checked = document.querySelector(`input[name="gst_q_${idx}"]:checked`);
                if (checked && parseInt(checked.value) === parseInt(q.correct)) score++;
            } else if (q.type === 'truefalse') {
                total++;
                const checked = document.querySelector(`input[name="gst_q_${idx}"]:checked`);
                if (checked && checked.value === String(q.correct)) score++;
            } else if (q.type === 'enumeration') {
                const inputs = document.querySelectorAll(`.q-block[data-idx="${idx}"] .enum-input`);
                let userAnswers = Array.from(inputs).map(inp => inp.value.trim());
                let correctAnswers = q.answers || [];
                let expectedCount = correctAnswers.length > 0 ? correctAnswers.length : (parseInt(q.count) || 1);
                total += expectedCount;
                
                let matches = 0;
                let anyOrder = q.anyOrder !== undefined ? q.anyOrder : true;
                let caseSensitive = q.caseSensitive || false;
                
                if (anyOrder) {
                    let matchedIndexes = new Set();
                    userAnswers.forEach(uAns => {
                        if (!uAns) return;
                        let u = caseSensitive ? uAns : uAns.toLowerCase();
                        for (let i = 0; i < correctAnswers.length; i++) {
                            if (matchedIndexes.has(i)) continue;
                            let c = caseSensitive ? correctAnswers[i].trim() : correctAnswers[i].trim().toLowerCase();
                            if (u === c && c !== "") { matches++; matchedIndexes.add(i); break; }
                        }
                    });
                } else {
                    for (let i = 0; i < correctAnswers.length; i++) {
                        if (i >= userAnswers.length) break;
                        let u = caseSensitive ? userAnswers[i] : userAnswers[i].toLowerCase();
                        let c = caseSensitive ? correctAnswers[i].trim() : correctAnswers[i].trim().toLowerCase();
                        if (u === c && c !== "") matches++;
                    }
                }
                score += matches;
            }
            else if (q.type === 'essay') {
                let pts = parseInt(q.points) || 1; 
                // Wait! GST essays cannot be manually graded later because GST results only store numeric score!
                // We'll just grant 0 points and count it toward total, or assume GST shouldn't use essays. 
                // For safety, add the max points to total so the math works, though GST essays are an edge case.
                total += pts; 
            }
        });


        // Determine starting grade
        let offset = GST_RULES.lowerGradesToStartBelow; // 0-15 = -3
        let category = "Requires Individual Assessment (Score 0-15)";
        let needsIndiv = 1;
        
        if (score >= GST_RULES.noIndividualAssessmentMin) {
            offset = 0;
            category = "Exempt from Individual Assessment (Score 28+)";
            needsIndiv = 0;
        } else if (score >= GST_RULES.middleRangeMin) {
            offset = GST_RULES.middleGradesToStartBelow; // 16-27 = -2
            category = "Requires Individual Assessment (Score 16-27)";
        }
        
        let startingGrade = Math.max(4, studentCurrentGrade - offset);

        gstResult = { score: score, totalItems: total, percentage: (score/total)*100, startingGrade: startingGrade, category: category, needsIndividualAssessment: needsIndiv, completed: true };

        const fd = new FormData();
        fd.append('score', score);
        fd.append('total', total);
        fd.append('starting_grade', startingGrade);
        fd.append('needs_individual', needsIndiv);
        fd.append('category', category);
        await fetch('api_assessment.php?action=submit_gst', { method: 'POST', body: fd });

        showGSTTransitionUI();
    });
}

function showGSTTransitionUI() {
    gstSection.classList.add('d-none');
    readerSection.classList.add('d-none');
    
    if (gstResult.score >= GST_RULES.noIndividualAssessmentMin) {
        showFinalProfileUI("Learner scored " + gstResult.score + " on GST. No individualized assessment required.");
        return;
    }

    if(transitionSection) {
        transitionSection.classList.remove('d-none');
        document.getElementById('transition-title').innerText = "GST RESULT";
        transitionContent.innerHTML = `
            <div class="text-left d-inline-block">
                <p><strong>Current Grade:</strong> Grade ${studentCurrentGrade}</p>
                <p><strong>GST Score:</strong> ${gstResult.score} / ${gstResult.totalItems}</p>
                <p><strong>Individualized Reading Assessment:</strong> <span class="text-danger">REQUIRED</span></p>
                <hr>
                <h4 class="text-primary mt-3"><strong>Starting Grade: GRADE ${gstResult.startingGrade}</strong></h4>
            </div>
        `;
        
        continueBtn.onclick = () => {
            transitionSection.classList.add('d-none');
            startGradedPassage(gstResult.startingGrade);
        };
    } else {
        startGradedPassage(gstResult.startingGrade);
    }
}

function showPassageTransitionUI(prevGrade, classification, wr, comp, nextGrade) {
    readerSection.classList.add('d-none');
    
    if(transitionSection) {
        transitionSection.classList.remove('d-none');
        document.getElementById('transition-title').innerText = `GRADE ${prevGrade} PASSAGE RESULT`;
        
        let html = `
            <div class="text-left d-inline-block">
                <h4 class="text-primary mb-3">Result: <strong>${classification}</strong></h4>
                <p><strong>Word Reading Accuracy:</strong> ${wr.toFixed(1)}%</p>
                <p><strong>Comprehension:</strong> ${comp.toFixed(1)}%</p>
                <hr>
        `;
        
        if (nextGrade !== null) {
            html += `<h4 class="text-success mt-3"><strong>Next Assessment: GRADE ${nextGrade}</strong></h4>`;
            continueBtn.onclick = () => {
                transitionSection.classList.add('d-none');
                startGradedPassage(nextGrade);
            };
        } else {
            html += `<h4 class="text-success mt-3"><strong>Reading Profile Complete</strong></h4>`;
            continueBtn.onclick = () => {
                transitionSection.classList.add('d-none');
                calculateFinalProfile();
                showFinalProfileUI("Reading profile complete.");
            };
        }
        
        html += `</div>`;
        transitionContent.innerHTML = html;
    } else {
        if(nextGrade !== null) {
            startGradedPassage(nextGrade);
        } else {
            calculateFinalProfile();
            showFinalProfileUI("Reading profile complete.");
        }
    }
}

// 4. CASCADE LOGIC
function determineNextAssessmentGrade() {
    if (Object.keys(testedGrades).length === 0) {
        return gstResult.startingGrade;
    }
    
    let hasInd = false, hasInst = false, hasFrus = false;
    let highestInd = -1, lowestFrus = 999;
    
    Object.keys(testedGrades).forEach(g => {
        let gr = parseInt(g);
        let cls = testedGrades[g].classification;
        if(cls === "Independent") { hasInd = true; if(gr > highestInd) highestInd = gr; }
        if(cls === "Instructional") hasInst = true;
        if(cls === "Frustration") { hasFrus = true; if(gr < lowestFrus) lowestFrus = gr; }
    });

    if (hasInd && hasInst && hasFrus) return null; // All found

    let lastTested = parseInt(currentTestingGrade || Object.keys(testedGrades)[Object.keys(testedGrades).length - 1]);
    let lastCls = testedGrades[lastTested].classification;

    let nextGrade = lastTested;
    if (lastCls === "Independent" || lastCls === "Instructional") {
        nextGrade = lastTested + 1;
    } else if (lastCls === "Frustration") {
        nextGrade = lastTested - 1;
    }

    if (testedGrades[nextGrade] || nextGrade > 10 || nextGrade < 4) {
        // If we need an Independent level but would go below Grade 4, we must stop.
        // If we need a Frustration level but would go above Grade 10, we must stop.
        return null; // Force exit if out of bounds
    }

    return nextGrade;
}

// 5. START PASSAGE
async function startGradedPassage(grade) {
    if(grade < 4 || grade > 10) {
        alert("System boundary reached. Proceeding to finalize profile.");
        calculateFinalProfile();
        showFinalProfileUI("Reading profile complete (Boundary Reached).");
        return;
    }
    
    currentTestingGrade = grade;
    const res = await fetch(`api_assessment.php?action=get_passage&grade=${grade}`);
    currentPassageData = await res.json();

    if(!currentPassageData || !currentPassageData.passage_text) {
        alert(`Missing passage for Grade ${grade}! Please ask your teacher to add this passage to the system. You can log out and resume right here once it's added.`);
        window.location.href = 'dashboard_student.php';
        return;
    }

    document.getElementById('level-indicator').innerText = `Grade ${grade} Passage`;
    const storyEl = document.getElementById('story');
    const storyEditor = document.getElementById('storyEditor');
    if(storyEditor) storyEditor.value = currentPassageData.passage_text;
    if(storyEl) {
        storyEl.innerHTML = currentPassageData.passage_text;
        if (window.originalStory !== undefined) window.originalStory = currentPassageData.passage_text;
    }

    readerSection.classList.remove('d-none');
    const resetBtn = document.getElementById('resetBtn');
    if (resetBtn) resetBtn.click();
}

window.addEventListener('assessmentComplete', (e) => {
    window.currentAssessment = e.detail.assessment;
    window.currentData = e.detail.data;
    if (!window.currentAssessment) return;
    renderComprehensionUI();
});

function renderComprehensionUI() {
    let allQ = [];
    try { allQ = JSON.parse(currentPassageData.questions_json || '[]'); } catch(e) {}
    questionsContainer.innerHTML = ''; 
    if (allQ.length === 0) {
        questionsContainer.innerHTML = '<p class="text-muted">No comprehension questions for this passage.</p>';
    }

    allQ.forEach((q, idx) => {
        let html = `<div class="mb-4 q-block bg-white p-4 border rounded" data-type="${q.type}" data-idx="${idx}">
            <h5 class="font-weight-bold mb-3">${idx+1}. ${q.question || q.text || ''}</h5>`;
        if (q.type === 'multichoice' || (!q.type && q.options)) {
            (q.options || []).forEach((opt, oIdx) => {
                html += `<div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="q_${idx}" value="${opt}" id="q_${idx}_${oIdx}">
                    <label class="form-check-label" for="q_${idx}_${oIdx}">${opt}</label>
                </div>`;
            });
        } else if (q.type === 'truefalse') {
            html += `<div class="form-check mb-2"><input class="form-check-input" type="radio" name="q_${idx}" value="true" id="q_${idx}_t"><label class="form-check-label" for="q_${idx}_t">True</label></div>
                     <div class="form-check mb-2"><input class="form-check-input" type="radio" name="q_${idx}" value="false" id="q_${idx}_f"><label class="form-check-label" for="q_${idx}_f">False</label></div>`;
        } else if (q.type === 'enumeration') {
            const count = parseInt(q.count) || 3;
            for(let c=0; c<count; c++) html += `<input type="text" class="form-control mb-2 enum-input" placeholder="Item ${c+1}">`;
        } else if (q.type === 'essay') {
            html += `<textarea class="form-control essay-input" rows="3" placeholder="Type your answer..."></textarea>`;
        }
        html += `</div>`;
        questionsContainer.innerHTML += html;
    });

    assessmentModal.style.display = 'block';
}

if(submitAssessmentBtn) {
    submitAssessmentBtn.addEventListener('click', async () => {
        submitAssessmentBtn.innerText = 'Scoring...';
        submitAssessmentBtn.disabled = true;

        let compCorrect = 0;
        let compTotal = 0;
        let studentAnswers = {};
        let allQ = JSON.parse(currentPassageData.questions_json || '[]');

        allQ.forEach((q, idx) => {
            if (q.type === 'multichoice') {
                compTotal++;
                const checked = document.querySelector(`input[name="q_${idx}"]:checked`);
                if (checked) studentAnswers[idx] = checked.value;
                if (checked && checked.value === q.options[parseInt(q.correct)]) compCorrect++;
            } else if (q.type === 'truefalse') {
                compTotal++;
                const checked = document.querySelector(`input[name="q_${idx}"]:checked`);
                if (checked) studentAnswers[idx] = checked.value;
                if (checked && checked.value === String(q.correct)) compCorrect++;
            } else if (q.type === 'enumeration') {
                const inputs = document.querySelectorAll(`.q-block[data-idx="${idx}"] .enum-input`);
                let userAnswers = Array.from(inputs).map(inp => inp.value.trim());
                studentAnswers[idx] = userAnswers;
                let correctAnswers = q.answers || [];
                let expectedCount = correctAnswers.length > 0 ? correctAnswers.length : (parseInt(q.count) || 1);
                compTotal += expectedCount;
                
                let matches = 0;
                let anyOrder = q.anyOrder !== undefined ? q.anyOrder : true;
                let caseSensitive = q.caseSensitive || false;
                
                if (anyOrder) {
                    let matchedIndexes = new Set();
                    userAnswers.forEach(uAns => {
                        if (!uAns) return;
                        let u = caseSensitive ? uAns : uAns.toLowerCase();
                        for (let i = 0; i < correctAnswers.length; i++) {
                            if (matchedIndexes.has(i)) continue;
                            let c = caseSensitive ? correctAnswers[i].trim() : correctAnswers[i].trim().toLowerCase();
                            if (u === c && c !== "") {
                                matches++;
                                matchedIndexes.add(i);
                                break;
                            }
                        }
                    });
                } else {
                    for (let i = 0; i < correctAnswers.length; i++) {
                        if (i >= userAnswers.length) break;
                        let u = caseSensitive ? userAnswers[i] : userAnswers[i].toLowerCase();
                        let c = caseSensitive ? correctAnswers[i].trim() : correctAnswers[i].trim().toLowerCase();
                        if (u === c && c !== "") {
                            matches++;
                        }
                    }
                }
                compCorrect += matches;
            }
            else if (q.type === 'essay') {
                const textarea = document.querySelector(`.q-block[data-idx="${idx}"] .essay-input`);
                if (textarea) studentAnswers[idx] = textarea.value.trim();
                let pts = parseInt(q.points) || 30;
                compTotal += pts; 
                // Student gets 0 points for essay by default until teacher grades it
            }
        });

        let compScorePercentage = compTotal > 0 ? (compCorrect / compTotal) * 100 : 100;
        
        // Calculate WPM and Accuracy identical to app.js renderPerformance()
        const duration = Number(window.currentData?.pipeline?.audioDurationSeconds) || 0;
        const spokenCount = window.currentAssessment?.spokenWords?.length || 0;
        const wpm = (duration > 0) ? (spokenCount / (duration / 60)) : 0;
        
        const counts = window.currentAssessment?.counts || {};
        const miscues = (counts.mispronunciation||0) + (counts.omission||0) + (counts.insertion||0) + (counts.substitution||0) + (counts.reversal||0);
        const words = window.currentAssessment?.referenceWords?.length || 0;
        const accuracyScore = words > 0 ? Math.max(0, ((words - miscues) / words) * 100) : 0;

        const classification = classifyPassage(accuracyScore, compScorePercentage);

        const fd = new FormData();
        fd.append('passage_grade', currentTestingGrade);
        fd.append('accuracy_score', accuracyScore);
        fd.append('comprehension_score', compScorePercentage);
        fd.append('classification', classification);
        fd.append('reading_time', duration);
        fd.append('reading_speed', wpm);
        fd.append('miscues_json', JSON.stringify(counts));
        fd.append('answers_json', JSON.stringify(studentAnswers));
        
        // Detailed evaluation data for teacher review
        const evalData = {
            assessment: window.currentAssessment,
            azure: window.currentData?.azure,
            realtime: window.currentData?.realtime,
            pipeline: window.currentData?.pipeline
        };
        fd.append('evaluation_data', JSON.stringify(evalData));
        
        if (window.lastRecordingBlob) {
            fd.append('audio_file', window.lastRecordingBlob, 'recording.webm');
        }
        
        await fetch('api_assessment.php?action=submit_attempt', { method: 'POST', body: fd });

        testedGrades[currentTestingGrade] = {
            classification: classification,
            wr: accuracyScore,
            comp: compScorePercentage
        };

        assessmentModal.style.display = 'none';
        submitAssessmentBtn.innerText = 'Submit Assessment';
        submitAssessmentBtn.disabled = false;

        const nextGrade = determineNextAssessmentGrade();
        showPassageTransitionUI(currentTestingGrade, classification, accuracyScore, compScorePercentage, nextGrade);
    });
}

function classifyPassage(wr, comp) {
    if (wr >= 97 && comp >= 80) return "Independent";
    if (wr <= 89 || comp <= 58) return "Frustration";
    return "Instructional";
}

function calculateFinalProfile() {
    Object.keys(testedGrades).forEach(g => {
        let cls = testedGrades[g].classification;
        if(cls === "Independent") readingProfile.independentGrade = g;
        if(cls === "Instructional") readingProfile.instructionalGrade = g;
        if(cls === "Frustration") readingProfile.frustrationGrade = g;
    });

    const fd = new FormData();
    fd.append('independent_grade', readingProfile.independentGrade || '');
    fd.append('instructional_grade', readingProfile.instructionalGrade || '');
    fd.append('frustration_grade', readingProfile.frustrationGrade || '');
    fetch('api_assessment.php?action=finalize_profile', { method: 'POST', body: fd });
}

function showFinalProfileUI(msg) {
    readerSection.classList.add('d-none');
    gstSection.classList.add('d-none');
    if(transitionSection) transitionSection.classList.add('d-none');
    
    const div = document.createElement('div');
    div.className = 'container text-center mt-5';
    div.innerHTML = `
        <div class="card shadow p-5">
            <h2 class="text-success mb-3"><i class="fas fa-check-circle"></i> Assessment Complete</h2>
            <p class="lead">${msg}</p>
            <hr>
            <h4 class="mt-4">Final Reading Profile</h4>
            <div class="row mt-4">
                <div class="col-4"><strong>Independent:</strong><br>Grade ${readingProfile.independentGrade || 'N/A'}</div>
                <div class="col-4"><strong>Instructional:</strong><br>Grade ${readingProfile.instructionalGrade || 'N/A'}</div>
                <div class="col-4"><strong>Frustration:</strong><br>Grade ${readingProfile.frustrationGrade || 'N/A'}</div>
            </div>
            <a href="dashboard_student.php" class="btn btn-primary mt-5">Return to Dashboard</a>
        </div>
    `;
    document.querySelector('.shell').appendChild(div);
}

document.addEventListener("DOMContentLoaded", () => {
    initializeAssessmentFlow();
});