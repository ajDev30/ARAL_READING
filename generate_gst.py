import json
import mysql.connector

# Base data to append
append_blocks = [
    {
        "type": "description",
        "question": "<p><strong>Reading Passage 1: The Solar System</strong></p><p>Our solar system consists of our star, the Sun, and everything bound to it by gravity. This includes the eight planets, dozens of moons, and millions of asteroids, comets, and meteoroids. The inner rocky planets are Mercury, Venus, Earth, and Mars. The outer planets are gas giants Jupiter and Saturn, and ice giants Uranus and Neptune. Beyond Neptune lies the Kuiper Belt, a region populated by icy bodies like Pluto. The sun contains 99.8% of the mass in the solar system, making its gravitational pull immense enough to keep all these objects in orbit.</p>"
    },
    {
        "type": "multichoice",
        "question": "<p>According to the passage, what holds the solar system together?</p>",
        "options": ["Magnetic fields", "Gravity", "Solar winds", "Dark matter"],
        "correct": 1
    },
    {
        "type": "multichoice",
        "question": "<p>Which of the following is considered an ice giant?</p>",
        "options": ["Jupiter", "Mars", "Neptune", "Venus"],
        "correct": 2
    },
    {
        "type": "multichoice",
        "question": "<p>How much of the solar system's mass is contained in the Sun?</p>",
        "options": ["50%", "75.5%", "90%", "99.8%"],
        "correct": 3
    },
    {
        "type": "multichoice",
        "question": "<p>What lies beyond Neptune?</p>",
        "options": ["The Asteroid Belt", "The Kuiper Belt", "The Milky Way core", "The Andromeda Galaxy"],
        "correct": 1
    },
    {
        "type": "multichoice",
        "question": "<p>Which planet is categorized as an inner rocky planet?</p>",
        "options": ["Saturn", "Jupiter", "Earth", "Uranus"],
        "correct": 2
    },
    {
        "type": "multichoice",
        "question": "<p>The main purpose of this passage is to:</p>",
        "options": ["Persuade readers to study astronomy", "Describe the components of our solar system", "Explain how gravity works", "Compare Earth to Mars"],
        "correct": 1
    },
    {
        "type": "description",
        "question": "<p><strong>Reading Passage 2: The History of the Olympic Games</strong></p><p>The ancient Olympic Games were originally a festival, or celebration, of and for Zeus. They were held in Olympia, a rural sanctuary site in the western Peloponnesos. The Games were held every four years, and during this time, a truce was announced so athletes could travel safely. The first modern Olympics took place in 1896 in Athens, Greece, and featured 280 participants from 13 nations, competing in 43 events. Unlike the ancient games which only allowed freeborn Greek men to participate, the modern games have grown to include male and female athletes from all over the world.</p>"
    },
    {
        "type": "multichoice",
        "question": "<p>Who were the ancient Olympic Games meant to celebrate?</p>",
        "options": ["Apollo", "Zeus", "Athena", "Poseidon"],
        "correct": 1
    },
    {
        "type": "multichoice",
        "question": "<p>Where were the first modern Olympics held?</p>",
        "options": ["Rome", "Paris", "Athens", "Olympia"],
        "correct": 2
    },
    {
        "type": "multichoice",
        "question": "<p>In what year did the first modern Olympics take place?</p>",
        "options": ["1896", "1900", "1800", "1924"],
        "correct": 0
    },
    {
        "type": "multichoice",
        "question": "<p>How frequently were the ancient Games held?</p>",
        "options": ["Every year", "Every two years", "Every four years", "Every decade"],
        "correct": 2
    },
    {
        "type": "multichoice",
        "question": "<p>Who was allowed to participate in the ancient Olympic games?</p>",
        "options": ["Anyone from the world", "Only freeborn Greek men", "Men and women from Greece", "Professional athletes only"],
        "correct": 1
    },
    {
        "type": "multichoice",
        "question": "<p>Why was a truce announced during the ancient Games?</p>",
        "options": ["To allow athletes safe travel", "To stop all wars permanently", "To honor the gods", "To allow farmers to harvest"],
        "correct": 0
    },
    {
        "type": "description",
        "question": "<p><strong>Reading Passage 3: The Benefits of Regular Exercise</strong></p><p>Regular physical activity is one of the most important things you can do for your health. Being physically active can improve your brain health, help manage weight, reduce the risk of disease, strengthen bones and muscles, and improve your ability to do everyday activities. Adults who sit less and do any amount of moderate-to-vigorous physical activity gain some health benefits. Furthermore, exercise promotes better sleep, boosting energy levels throughout the day. It also stimulates various brain chemicals that may leave you feeling happier, more relaxed, and less anxious.</p>"
    },
    {
        "type": "multichoice",
        "question": "<p>According to the passage, what is one mental benefit of exercise?</p>",
        "options": ["It increases anxiety", "It stimulates brain chemicals that make you happier", "It makes you sleep less", "It decreases your ability to relax"],
        "correct": 1
    },
    {
        "type": "multichoice",
        "question": "<p>What effect does exercise have on everyday activities?</p>",
        "options": ["It makes them harder", "It has no effect", "It improves your ability to do them", "It makes them unnecessary"],
        "correct": 2
    },
    {
        "type": "multichoice",
        "question": "<p>How does physical activity affect bones and muscles?</p>",
        "options": ["It weakens them", "It strengthens them", "It makes them heavier", "It causes them to shrink"],
        "correct": 1
    },
    {
        "type": "multichoice",
        "question": "<p>The passage suggests that adults should:</p>",
        "options": ["Sit more often", "Avoid vigorous activities", "Sit less and do physical activity", "Only do light stretching"],
        "correct": 2
    },
    {
        "type": "multichoice",
        "question": "<p>How does exercise affect sleep, according to the text?</p>",
        "options": ["It causes insomnia", "It promotes better sleep", "It makes you sleep too much", "It has no connection to sleep"],
        "correct": 1
    },
    {
        "type": "multichoice",
        "question": "<p>What is the overall tone of this passage?</p>",
        "options": ["Informative and encouraging", "Negative and warning", "Humorous", "Argumentative"],
        "correct": 0
    },
    {
        "type": "description",
        "question": "<p><strong>Reading Passage 4: The Invention of the Telephone</strong></p><p>While Alexander Graham Bell is most often credited with the invention of the telephone, the true history is much more complex. Antonio Meucci, an Italian immigrant, began developing the design of a talking telegraph in 1849. Elisha Gray also designed a device that could transmit speech electrically. However, Bell was the first to secure the patent for the telephone in 1876. Bell's famous first words transmitted through the device were to his assistant: \"Mr. Watson, come here, I want to see you.\" This invention revolutionized global communication, making the world feel much smaller.</p>"
    },
    {
        "type": "multichoice",
        "question": "<p>Who is most often credited with inventing the telephone?</p>",
        "options": ["Antonio Meucci", "Elisha Gray", "Thomas Edison", "Alexander Graham Bell"],
        "correct": 3
    },
    {
        "type": "multichoice",
        "question": "<p>In what year did Bell secure the patent for the telephone?</p>",
        "options": ["1849", "1876", "1896", "1900"],
        "correct": 1
    },
    {
        "type": "multichoice",
        "question": "<p>Who was the Italian immigrant that began developing a talking telegraph in 1849?</p>",
        "options": ["Leonardo da Vinci", "Guglielmo Marconi", "Antonio Meucci", "Enrico Fermi"],
        "correct": 2
    },
    {
        "type": "multichoice",
        "question": "<p>Who did Bell speak to during the first transmission?</p>",
        "options": ["Elisha Gray", "Mr. Watson", "His wife", "Antonio Meucci"],
        "correct": 1
    },
    {
        "type": "multichoice",
        "question": "<p>What did the invention of the telephone do for the world?</p>",
        "options": ["It revolutionized global communication", "It made writing letters obsolete immediately", "It caused a war", "It slowed down business"],
        "correct": 0
    },
    {
        "type": "multichoice",
        "question": "<p>What is the meaning of 'revolutionary' as implied in the text?</p>",
        "options": ["Violent", "Causing a dramatic change", "Spinning in circles", "Ineffective"],
        "correct": 1
    },
    {
        "type": "description",
        "question": "<p><strong>Reading Passage 5: The Ocean's Ecosystem</strong></p><p>The ocean is the largest ecosystem on Earth, covering more than 70% of the planet's surface. It is home to a vast diversity of life, from microscopic phytoplankton to the massive blue whale. Phytoplankton form the base of the marine food web, using sunlight to produce energy through photosynthesis. Small fish eat these plankton, and they in turn are eaten by larger predators like sharks and dolphins. Coral reefs, often called the 'rainforests of the sea,' support a quarter of all marine species despite covering less than 1% of the ocean floor. Protecting these habitats is crucial for marine biodiversity.</p>"
    },
    {
        "type": "multichoice",
        "question": "<p>What percentage of the Earth's surface is covered by the ocean?</p>",
        "options": ["30%", "50%", "More than 70%", "90%"],
        "correct": 2
    },
    {
        "type": "multichoice",
        "question": "<p>What forms the base of the marine food web?</p>",
        "options": ["Coral reefs", "Sharks", "Blue whales", "Phytoplankton"],
        "correct": 3
    },
    {
        "type": "multichoice",
        "question": "<p>How do phytoplankton produce energy?</p>",
        "options": ["By eating small fish", "Through photosynthesis", "By consuming coral", "By swimming constantly"],
        "correct": 1
    },
    {
        "type": "multichoice",
        "question": "<p>What are coral reefs often compared to?</p>",
        "options": ["The desert", "The rainforests of the sea", "The deep abyss", "Mountains"],
        "correct": 1
    },
    {
        "type": "multichoice",
        "question": "<p>How much of the ocean floor do coral reefs cover?</p>",
        "options": ["Less than 1%", "10%", "25%", "50%"],
        "correct": 0
    },
    {
        "type": "multichoice",
        "question": "<p>Why is protecting coral reefs important?</p>",
        "options": ["They provide drinking water", "They support a quarter of all marine species", "They prevent earthquakes", "They are a good source of oil"],
        "correct": 1
    }
]

# Connect to database
try:
    conn = mysql.connector.connect(
        host="aral_reading-db-1",
        user="root",
        password="rootpassword",
        database="aral_reading_db"
    )
    cursor = conn.cursor(dictionary=True)
    
    cursor.execute("SELECT id, grade_level, questions_json FROM gst_assessments")
    rows = cursor.fetchall()
    
    for row in rows:
        q = json.loads(row['questions_json']) if row['questions_json'] else []
        
        # Remove the previous generic items we added
        # We look for items containing "This is an additional question"
        clean_q = []
        for item in q:
            if item.get("type") == "multichoice" and "This is an additional question" in item.get("question", ""):
                continue
            clean_q.append(item)
        
        # Count current MCQs
        mcq_count = sum(1 for item in clean_q if item.get("type") == "multichoice")
        
        # If we have less than 40 MCQs, append the new blocks
        if mcq_count < 40:
            # How many more do we need?
            needed = 40 - mcq_count
            
            # Since our new append_blocks has exactly 30 MCQs, we can just append the whole block
            # But let's dynamically adjust numbers in the HTML.
            current_num = mcq_count + 1
            
            blocks_to_add = []
            for item in append_blocks:
                new_item = item.copy()
                if new_item['type'] == 'multichoice':
                    # Replace the <p> with numbered <p>
                    # For example, <p>According to... -> <p>11. According to...
                    new_item['question'] = new_item['question'].replace('<p>', f'<p>{current_num}. ')
                    current_num += 1
                blocks_to_add.append(new_item)
                
            clean_q.extend(blocks_to_add)
            
            # Save back to DB
            update_sql = "UPDATE gst_assessments SET questions_json = %s WHERE id = %s"
            cursor.execute(update_sql, (json.dumps(clean_q), row['id']))
            conn.commit()
            print(f"Updated {row['grade_level']} with new passages. Total MCQs: {current_num - 1}")
            
except Exception as e:
    print(f"Error: {e}")
finally:
    if 'conn' in locals() and conn.is_connected():
        cursor.close()
        conn.close()

