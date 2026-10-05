const fs = require('fs');
let code = fs.readFileSync('v536/public/cascading-flow.js', 'utf8');

// Exclude essay from total auto-gradable count
code = code.replace(
    "let total = currentQuestions.filter(q => q.type !== 'instruction').length;",
    "let total = currentQuestions.filter(q => q.type !== 'instruction' && q.type !== 'essay').length;"
);

fs.writeFileSync('v536/public/cascading-flow.js', code);
