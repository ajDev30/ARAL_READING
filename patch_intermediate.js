const fs = require('fs');
let file = '/root/ARAL_READING/v536/public/cascading-flow.js';
let content = fs.readFileSync(file, 'utf8');

const target = `
        let html = \`
            <div class="text-left d-inline-block">
                <h4 class="text-primary mb-3">Result: <strong>\${classification}</strong></h4>
                <p><strong>Word Reading Accuracy:</strong> \${wr.toFixed(1)}%</p>
                <p><strong>Comprehension:</strong> \${comp.toFixed(1)}%</p>
                <hr>
        \`;
`;

const replacement = `
        let explanation = "";
        if (classification === "Independent") {
            explanation = "You can read and understand the text with ease (Accuracy &ge; 97% and Comprehension &ge; 80%).";
        } else if (classification === "Instructional") {
            explanation = "You can read the text but may need teacher assistance (Accuracy 90-96% or Comprehension 59-79%).";
        } else if (classification === "Frustration" || classification === "Non-Reader") {
            explanation = "The text is too difficult (Accuracy &le; 89% or Comprehension &le; 58%).";
        }

        let html = \`
            <div class="text-left d-inline-block">
                <h4 class="text-primary mb-3">Result: <strong>\${classification}</strong></h4>
                <p><strong>Word Reading Accuracy:</strong> \${wr.toFixed(1)}%</p>
                <p><strong>Comprehension:</strong> \${comp.toFixed(1)}%</p>
                <div class="alert alert-secondary text-muted" style="font-size: 0.9em; padding: 10px; margin-top: 10px;">
                    <i class="fas fa-info-circle"></i> <strong>Why?</strong> \${explanation}
                </div>
                <hr>
        \`;
`;

content = content.replace(target, replacement);
fs.writeFileSync(file, content, 'utf8');
console.log("Updated cascading-flow.js");

let file2 = '/root/ARAL_READING/rewrite_cascading.php';
let content2 = fs.readFileSync(file2, 'utf8');
content2 = content2.replace(target, replacement);
fs.writeFileSync(file2, content2, 'utf8');
console.log("Updated rewrite_cascading.php");
