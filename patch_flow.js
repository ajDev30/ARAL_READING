const fs = require('fs');
let code = fs.readFileSync('v536/public/cascading-flow.js', 'utf8');

// Don't filter out 'instruction'
// (It doesn't say 'instruction', it just filters 'description', so 'instruction' passes anyway!)

// Add rendering for 'instruction' inside the loop
const insertHtml = `
            if (q.type === 'instruction') {
                html = \`<div class="mb-4 q-block" data-idx="\${i}">
                    <p class="text-blue-700 bg-blue-50 p-3 rounded border border-blue-100">\${q.text || ''}</p>
                </div>\`;
            } else if (q.type === 'multichoice') {
`;
code = code.replace(/if \(q\.type === 'multichoice'\) \{/, insertHtml);

// Make sure Q numbers don't increment for instructions
// Wait, the Q number is currently hardcoded as `<strong>Q${i+1}:</strong>` for everything.
// Let's fix the question rendering header.
const oldHeader = "let html = `<div class=\"mb-4 q-block\" data-idx=\"${i}\">\\n                <p><strong>Q${i+1}:</strong> ${q.question || q.text || 'Question text missing'}</p>`;";
const newHeader = `
            let html = '';
            if (q.type === 'instruction') {
                html = \`<div class="mb-4 q-block" data-idx="\${i}">
                    <p class="text-slate-600 bg-slate-50 p-3 rounded border border-slate-200 italic">\${q.text || ''}</p>
                </div>\`;
            } else if (q.type === 'essay') {
                html = \`<div class="mb-4 q-block" data-idx="\${i}">
                    <p><strong>Q\${i+1}:</strong> \${q.question || q.text || 'Question text missing'}</p>
                    <textarea class="form-control" name="q_\${i}_essay" rows="4" placeholder="Write your answer here..."></textarea>
                </div>\`;
            } else {
                html = \`<div class="mb-4 q-block" data-idx="\${i}">
                    <p><strong>Q\${i+1}:</strong> \${q.question || q.text || 'Question text missing'}</p>\`;
`;
code = code.replace(oldHeader, newHeader);

// In the grading section, make sure instruction doesn't add to "total" and "essay" saves its text
const oldTotal = "let total = currentQuestions.length;";
const newTotal = "let total = currentQuestions.filter(q => q.type !== 'instruction').length;";
code = code.replace(oldTotal, newTotal);

const essayGrading = `
        } else if (q.type === 'essay') {
            const txt = document.querySelector(\`textarea[name="q_\${i}_essay"]\`);
            if(txt) answers[i] = txt.value;
`;
code = code.replace(/\} else if \(q\.type === 'enumeration'\) \{/, essayGrading + "} else if (q.type === 'enumeration') {");

fs.writeFileSync('v536/public/cascading-flow.js', code);
