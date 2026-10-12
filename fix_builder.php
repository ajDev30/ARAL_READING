<?php
$f = "/var/www/andrew/ARAL_READING/manage_preassessment.php";
$c = file_get_contents($f);

// Fix multiple choice type and correct answer index
$c = str_replace(
    "result.push({ type: 'multiple_choice', question: q, options: options, answer: answer });",
    "result.push({ type: 'multichoice', question: q, options: options, correct: options.indexOf(answer) });",
    $c
);
$c = str_replace("addBlock('multiple_choice')", "addBlock('multichoice')", $c);
$c = str_replace("data-type=\"multiple_choice\"", "data-type=\"multichoice\"", $c);
$c = str_replace("type === 'multiple_choice'", "type === 'multichoice'", $c);

// Fix matching pairs keys
$c = str_replace(
    "premise: row.querySelectorAll('input')[0].value,",
    "question: row.querySelectorAll('input')[0].value,",
    $c
);
$c = str_replace(
    "response: row.querySelectorAll('input')[1].value",
    "answer: row.querySelectorAll('input')[1].value",
    $c
);

// Fix enumeration count key
$c = str_replace("expected_count: parseInt", "count: parseInt", $c);

// Also fix how matching loads from JSON to UI
$c = str_replace("p.premise", "p.question", $c);
$c = str_replace("p.response", "p.answer", $c);

file_put_contents($f, $c);
echo "Fixed builder JSON schema\n";
