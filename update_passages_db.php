<?php
require_once 'config.php';

$passages = [
    'Grade 4' => [
        'title' => 'Grade 4 — The Lost Puppy',
        'passage_text' => "Maria was walking home from school when she heard a soft sound near a small store. She looked behind a box and saw a little brown puppy. The puppy looked tired and hungry.\nMaria gave the puppy some water from her bottle. She wanted to take it home, but she knew someone might be looking for it. She asked the store owner if he knew the puppy.\nThe store owner said that he had seen the puppy several times that week. Maria decided to make a small sign that said, “Found Puppy.” She placed the sign near the store.\nThe next morning, a boy named Daniel came to the store. He was looking for his lost puppy. When Daniel saw the puppy, he smiled and hugged it.",
        'questions_json' => json_encode([
            ["type" => "multichoice", "question" => "Where did Maria find the puppy?", "options" => ["At school", "Behind a box near a store", "In her house", "In a park"], "correct" => "1"],
            ["type" => "multichoice", "question" => "What did Maria give the puppy?", "options" => ["Food", "Milk", "Water", "A toy"], "correct" => "2"],
            ["type" => "multichoice", "question" => "Why did Maria not take the puppy home immediately?", "options" => ["She was afraid of it.", "She thought someone might be looking for it.", "She did not like puppies.", "She was going to school."], "correct" => "1"],
            ["type" => "multichoice", "question" => "What did Maria make?", "options" => ["A toy", "A box", "A “Found Puppy” sign", "A house"], "correct" => "2"],
            ["type" => "multichoice", "question" => "Who owned the puppy?", "options" => ["Maria", "The store owner", "Daniel", "Maria's teacher"], "correct" => "2"]
        ])
    ],
    'Grade 5' => [
        'title' => 'Grade 5 — The Community Garden',
        'passage_text' => "Every Saturday morning, the students of Riverside Elementary helped care for a small community garden. The garden had vegetables, flowers, and several young fruit trees.\nOne Saturday, the students noticed that some of the plants were beginning to dry. Their teacher explained that the weather had been unusually hot and that the plants needed more water.\nInstead of using only the school's water supply, the students collected rainwater in large containers. They used the collected water to care for the plants.\nAfter several weeks, the garden became healthy again. The students also harvested tomatoes, eggplants, and other vegetables. They shared the vegetables with their families and the school's canteen.\nThe students learned that working together could help solve problems in their community.",
        'questions_json' => json_encode([
            ["type" => "multichoice", "question" => "What did the students care for?", "options" => ["A playground", "A community garden", "A library", "A river"], "correct" => "1"],
            ["type" => "multichoice", "question" => "Why were some plants drying?", "options" => ["There was too much rain.", "The weather was unusually hot.", "The students removed them.", "Animals ate them."], "correct" => "1"],
            ["type" => "multichoice", "question" => "What did the students collect?", "options" => ["Leaves", "Soil", "Rainwater", "Flowers"], "correct" => "2"],
            ["type" => "multichoice", "question" => "Which vegetable was mentioned in the passage?", "options" => ["Carrots", "Potatoes", "Tomatoes", "Corn"], "correct" => "2"],
            ["type" => "multichoice", "question" => "What lesson did the students learn?", "options" => ["Gardens are difficult to maintain.", "Working together can solve problems.", "Vegetables should not be shared.", "Rainwater cannot be used for plants."], "correct" => "1"]
        ])
    ],
    'Grade 6' => [
        'title' => 'Grade 6 — A New Library',
        'passage_text' => "When the old library in Green Valley School became too small, the principal asked the students what could be done. Many students suggested building a larger reading area.\nThe school could not afford to build a completely new building, so the teachers and parents looked for another solution. They decided to renovate an unused classroom.\nStudents helped organize old books, while parents repaired shelves and painted the walls. Local businesses donated additional books, tables, and chairs.\nAfter two months, the unused classroom had become a comfortable reading room. Students could now read during breaks and after classes.\nThe project showed the students that a community can accomplish great things when people contribute their time and resources.",
        'questions_json' => json_encode([
            ["type" => "multichoice", "question" => "Why did the school need a new reading area?", "options" => ["The old library was too small.", "The school had no teachers.", "Students did not like reading.", "The old library had no books."], "correct" => "0"],
            ["type" => "multichoice", "question" => "Why didn't the school build a completely new building?", "options" => ["There was no space.", "The school could not afford it.", "The principal did not want one.", "Parents disagreed."], "correct" => "1"],
            ["type" => "multichoice", "question" => "What did the parents do?", "options" => ["Wrote books", "Repaired shelves and painted", "Taught classes", "Sold vegetables"], "correct" => "1"],
            ["type" => "multichoice", "question" => "Who donated additional materials?", "options" => ["Local businesses", "Only students", "The principal", "Another school"], "correct" => "0"],
            ["type" => "multichoice", "question" => "What is the main idea of the passage?", "options" => ["Libraries are expensive.", "Students dislike old classrooms.", "Cooperation can help a community accomplish a goal.", "Schools should build new buildings."], "correct" => "2"]
        ])
    ],
    'Grade 7' => [
        'title' => 'Grade 7 — The Young Inventor',
        'passage_text' => "Leo enjoyed taking apart broken household objects to understand how they worked. His parents often reminded him to put the objects back together, but they noticed that he was naturally curious about machines.\nOne summer, Leo became interested in the amount of electricity used by lights that were accidentally left on. He designed a simple device that could detect when a room was empty and automatically turn off the lights.\nHis first design did not work properly. Sometimes the lights turned off while someone was still in the room. Instead of giving up, Leo tested different sensors and adjusted his design.\nAfter several attempts, he developed a working model. His science teacher encouraged him to present it at the school's invention fair.\nLeo did not win first place, but he learned that successful inventions often require patience, testing, and improvement.",
        'questions_json' => json_encode([
            ["type" => "multichoice", "question" => "What was Leo interested in?", "options" => ["Cooking", "Machines", "Sports", "Gardening"], "correct" => "1"],
            ["type" => "multichoice", "question" => "What problem did Leo want to solve?", "options" => ["Wasted electricity", "Lack of water", "Broken furniture", "Traffic"], "correct" => "0"],
            ["type" => "multichoice", "question" => "Why did Leo's first design fail?", "options" => ["He had no materials.", "The sensors sometimes detected the room incorrectly.", "His teacher rejected it.", "He stopped working on it."], "correct" => "1"],
            ["type" => "multichoice", "question" => "What did Leo do after his first attempt failed?", "options" => ["He gave up.", "He sold the device.", "He tested and adjusted the design.", "He threw it away."], "correct" => "2"],
            ["type" => "multichoice", "question" => "What lesson did Leo learn?", "options" => ["Winning is the most important goal.", "Inventions never fail.", "Successful inventions require patience and improvement.", "Science is easy."], "correct" => "2"]
        ])
    ],
    'Grade 8' => [
        'title' => 'Grade 8 — The Hidden World of Coral Reefs',
        'passage_text' => "Coral reefs are among the most diverse ecosystems in the ocean. Although they cover only a small portion of the sea floor, they provide shelter and food for thousands of marine species.\nCorals may look like colorful rocks, but they are actually living organisms. Tiny animals called coral polyps build hard structures that eventually form reefs.\nCoral reefs are important to people as well. They can help protect coastlines from strong waves and provide income for communities through fishing and tourism.\nHowever, reefs face serious threats. Rising ocean temperatures, pollution, destructive fishing practices, and other environmental changes can damage coral ecosystems.\nProtecting coral reefs requires cooperation among governments, communities, scientists, and individuals. Reducing pollution and using marine resources responsibly can help preserve these ecosystems for future generations.",
        'questions_json' => json_encode([
            ["type" => "multichoice", "question" => "What are coral reefs?", "options" => ["Nonliving rocks", "Diverse ocean ecosystems", "Artificial structures", "Underwater buildings"], "correct" => "1"],
            ["type" => "multichoice", "question" => "What creates the hard structures of coral reefs?", "options" => ["Fish", "Seaweed", "Coral polyps", "Waves"], "correct" => "2"],
            ["type" => "multichoice", "question" => "How can coral reefs benefit coastal communities?", "options" => ["They provide income through fishing and tourism.", "They eliminate storms completely.", "They create freshwater.", "They prevent all pollution."], "correct" => "0"],
            ["type" => "multichoice", "question" => "Which is a threat to coral reefs?", "options" => ["Responsible fishing", "Rising ocean temperatures", "Scientific research", "Marine conservation"], "correct" => "1"],
            ["type" => "multichoice", "question" => "Why does protecting coral reefs require cooperation?", "options" => ["No single group can address all environmental threats alone.", "Coral reefs belong to one country.", "Only scientists can protect them.", "Tourism must be stopped."], "correct" => "0"]
        ])
    ],
    'Grade 9' => [
        'title' => 'Grade 9 — Artificial Intelligence in Education',
        'passage_text' => "Artificial intelligence is becoming increasingly common in education. Digital systems can help students practice skills, receive immediate feedback, and access learning materials.\nFor example, an educational system can analyze a student's answers and identify areas where the student is having difficulty. A teacher can then use this information to provide more appropriate support.\nHowever, artificial intelligence should not completely replace teachers. Teachers understand factors that automated systems may not recognize, such as a student's motivation, personal circumstances, or difficulties outside the classroom.\nThere are also concerns about privacy and responsible use of technology. Schools need clear policies to ensure that student information is protected.\nArtificial intelligence can therefore be useful in education when it is treated as a tool that supports teachers and students rather than as a replacement for human judgment.",
        'questions_json' => json_encode([
            ["type" => "multichoice", "question" => "What can AI systems provide to students?", "options" => ["Immediate feedback", "Free transportation", "School buildings", "Sports equipment"], "correct" => "0"],
            ["type" => "multichoice", "question" => "How can an AI system help teachers?", "options" => ["By replacing them", "By identifying areas where students struggle", "By making all classroom decisions", "By eliminating examinations"], "correct" => "1"],
            ["type" => "multichoice", "question" => "Why should teachers not be completely replaced by AI?", "options" => ["Teachers understand personal factors that automated systems may not recognize.", "AI cannot store information.", "Teachers cannot use technology.", "Students dislike computers."], "correct" => "0"],
            ["type" => "multichoice", "question" => "What concern is mentioned in the passage?", "options" => ["Food safety", "Privacy", "Transportation", "Sports"], "correct" => "1"],
            ["type" => "multichoice", "question" => "What is the author's main point?", "options" => ["AI should replace teachers.", "AI has no place in education.", "AI can support education but should be used responsibly.", "Students should stop using technology."], "correct" => "2"]
        ])
    ],
    'Grade 10' => [
        'title' => 'Grade 10 — Preserving History Through Archaeology',
        'passage_text' => "Archaeology helps communities understand how people lived in the past. Archaeologists study objects, structures, and other evidence left behind by earlier societies.\nAn archaeological site may contain pottery, tools, human remains, or foundations of buildings. Each discovery can provide information about the activities, beliefs, technology, and environment of the people who once lived there.\nHowever, archaeological sites are vulnerable to damage. Construction, illegal excavation, natural processes, and careless collection of artifacts can destroy valuable evidence. Once an archaeological site is damaged, important information may be lost permanently.\nPreserving archaeological sites therefore requires careful cooperation among researchers, government agencies, local communities, and the public. Communities can help by reporting discoveries, avoiding unauthorized excavation, and supporting responsible preservation programs.\nArchaeology is not simply about finding old objects. It is about protecting evidence that can help future generations understand their history.",
        'questions_json' => json_encode([
            ["type" => "multichoice", "question" => "What is the primary purpose of archaeology according to the passage?", "options" => ["To sell historical objects", "To understand how people lived in the past", "To build new communities", "To find valuable materials"], "correct" => "1"],
            ["type" => "multichoice", "question" => "Which of the following may be found at an archaeological site?", "options" => ["Modern computers", "Pottery and tools", "New vehicles", "Plastic packaging"], "correct" => "1"],
            ["type" => "multichoice", "question" => "Why is damage to archaeological sites serious?", "options" => ["It makes construction more expensive.", "Important historical information may be permanently lost.", "Archaeologists will have more work.", "Communities will lose tourists."], "correct" => "1"],
            ["type" => "multichoice", "question" => "What can communities do to help preserve archaeological sites?", "options" => ["Conduct unauthorized excavations", "Collect artifacts for personal use", "Report discoveries and support responsible preservation", "Remove historical objects from sites"], "correct" => "2"],
            ["type" => "multichoice", "question" => "Which statement best expresses the author's purpose?", "options" => ["To explain why archaeological evidence should be protected", "To encourage people to collect artifacts", "To describe modern construction methods", "To compare different countries"], "correct" => "0"]
        ])
    ]
];

foreach ($passages as $grade_level => $data) {
    // Check if it exists
    $stmt = $pdo->prepare("SELECT id FROM reading_passages WHERE grade_level = ?");
    $stmt->execute([$grade_level]);
    if ($stmt->rowCount() > 0) {
        $update = $pdo->prepare("UPDATE reading_passages SET title = ?, passage_text = ?, questions_json = ? WHERE grade_level = ?");
        $update->execute([$data['title'], $data['passage_text'], $data['questions_json'], $grade_level]);
    } else {
        $insert = $pdo->prepare("INSERT INTO reading_passages (grade_level, title, passage_text, questions_json) VALUES (?, ?, ?, ?)");
        $insert->execute([$grade_level, $data['title'], $data['passage_text'], $data['questions_json']]);
    }
}

echo "<div style='padding: 20px; font-family: sans-serif; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 5px; margin: 20px;'>";
echo "<strong>Success!</strong> All Grade 4 to Grade 10 reading passages and questions have been updated in the database.";
echo "</div>";
?>
